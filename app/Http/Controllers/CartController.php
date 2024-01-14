<?php

namespace App\Http\Controllers;


use App\Http\Requests\CartRequest;
use App\Http\Resources\AdminCartResource;
use App\Http\Resources\CartResource;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use UltraMsg\WhatsAppApi;

class CartController extends Controller
{
    /**
     * Returns all carts belong to current user
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $carts = auth()->user()->carts()->with('orders')->latest()->get();
        if (!count($carts)) {
            return $this->failed('There are not orders yet', 404);
        }
        return $this->success(CartResource::collection($carts));
    }

    /**
     * Returns all users with their carts
     *
     * @return JsonResponse
     */
    public function allCarts(): JsonResponse
    {
        $carts = Cart::all();
        if (!count($carts)) {
            return $this->failed('There are not orders yet');
        }
        return $this->success(AdminCartResource::collection($carts));
    }

    /**
     * Creates a Cart
     *
     * @param CartRequest $request
     * @return JsonResponse
     */
    public function store(CartRequest $request): JsonResponse
    {
        $data = $request->validated();
        $orders = $data['orders'];
        $user = User::find(auth()->id());
        $number = $user->number_of_orders++;

        $cart = Cart::query()->create([
            'user_id' => auth()->id(),
            'status' => 1,
            'paid' => false,
            'number' => $number,
            'total' => 0,
            'amount' => 0
        ]);
        $total = 0;
        $amount = 0;
        $products = [];
        foreach ($orders as $order) {
            $products[$order['id']] = Product::find($order['id']);
            if (!$products[$order['id']]) {
                $cart->delete();
                return $this->failed('There is a product does not exist', 404);
            }
            if ($products[$order['id']]->quantity < $order['quantity']) {
                $cart->delete();
                return $this->failed('There are not enough products');
            }
            $products[$order['id']]->quantity -= $order['quantity'];
            $amount += $order['quantity'];
            $total += $order['quantity'] * $products[$order['id']]->price;
            Order::query()->create([
                'product_id' => $order['id'],
                'quantity' => $order['quantity'],
                'total' => $order['quantity'] * $products[$order['id']]->price,
                'cart_id' => $cart['id']
            ]);
        }
        foreach ($products as $product) {
            $product->save();
        }
        $user->save();
        $cart->total = $total;
        $cart->amount = $amount;
        $cart->save();
        return $this->success(null, "Cart has been created successfully");
    }

    /**
     * Deletes a Cart by user
     *
     * @param Cart $cart
     * @return JsonResponse
     */
    public function destroy(Cart $cart): JsonResponse
    {
        if ($cart['user']['id'] != Auth::id()) {
            return $this->failed('You can not delete this order');
        }

        if ($cart['status'] != 1) {
            return $this->failed('You can not delete your order now');
        }
        $orders = $cart['orders'];
        foreach ($orders as $order) {
            $product = $order['product'];
            $product['quantity'] = $product['quantity'] + $order['quantity'];
            $product->save();

        }
        $cart->delete();

        return $this->success(null, "Cart has been deleted successfully");
    }


    public function cancelOrder(Cart $cart): JsonResponse
    {
        if ($cart['paid'] || $cart['status'] == 3 || $cart['status'] == 4) {
            return $this->failed('You can not cancel your order now');
        }
        $orders = $cart['orders'];
        foreach ($orders as $order) {
            $product = $order['product'];
            $product['quantity'] = $product['quantity'] + $order['quantity'];
            $product->save();
        }
        $cart->delete();

        $notification = Notification::query()->create([
            'user_id' => $cart['user']['id'],
            'title' => $cart['number'],
            'body' => 'cancel_cart'
        ]);
        app()->setLocale($cart->user->lang);
        User::find($cart['user_id'])->sendNotification($notification['title'], trans($notification['body']));
        app()->setLocale(Auth::user()->lang);
        return $this->success(null, "Cart has been deleted successfully");
    }

    public function setAsPaid(Cart $cart): JsonResponse
    {
        if ($cart['paid']) {
            return $this->failed('this order is already paid');
        }
        $cart->update([
            'paid' => true
        ]);
        $notification = Notification::query()->create([
            'user_id' => $cart['user']['id'],
            'title' => $cart['number'],
            'body' => 'paid_cart'
        ]);

        app()->setLocale($cart->user->lang);
        User::find($cart['user_id'])->sendNotification($notification['title'], trans($notification['body']));
        app()->setLocale(Auth::user()->lang);
        return $this->success(null);
    }

    /*
     *   status of cart
     *
     *   1 : requesting
     *   2 : in preparation
     *   3 : sending
     *   4 : delivered
     */
    public function nextStatus(Cart $cart): JsonResponse
    {
        if ($cart['status'] == 4) {
            return $this->failed('no next status');
        }
        $cart->update([
            'status' => $cart['status'] + 1
        ]);
        $body = "";
        switch ($cart['status']) {
            case 2 :
                $body = 'preparation';
                break;
            case 3 :
                $body = 'sent';
                break;
            case 4 :
                $body = 'delivered';
        }
        $notification = Notification::query()->create([
            'user_id' => $cart['user']['id'],
            'title' => $cart['number'],
            'body' => $body
        ]);
        app()->setLocale($cart->user->lang);
        User::find($cart['user_id'])->sendNotification($notification['title'], trans($notification['body']));
        app()->setLocale(Auth::user()->lang);
        return $this->success(null);
    }


    public function getSalesThisMonth(): JsonResponse
    {
        $sales = Cart::query()
            ->whereMonth('created_at', '=', Carbon::now()->month)
            ->where('status', '!=', 1)
            ->select('total')
            ->get();
        if (!$sales) return $this->success(['total' => 0]);
        $total = 0.0;
        foreach ($sales as $s) {
            $total += $s['total'];
        }
        return $this->success(['total' => $total]);
    }

    /**
     * @param string $day
     * @return Collection|array
     */
    public function getCartsOnDay(string $day): Collection|array
    {
        return Cart::query()
            ->where('status', '!=', 1)
            ->whereDate('created_at', '=', date('y-m-d', strtotime("last $day")))
            ->get();
    }

    /**
     * @return JsonResponse
     */
    public function getSalesLastWeek(): JsonResponse
    {
        $days = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday"
        ];
        $sum = 0;
        $sales = [];
        foreach ($days as $day) {
            $sumForDay = 0;
            $carts = $this->getCartsOnDay($day);
            if (!count($carts)) {
                $sales[] = 0;
            } else {
                foreach ($carts as $cart) {
                    $sumForDay += $cart['total'];
                }
                $sum += $sumForDay;
                $sales[] = $sumForDay;
            }
        }
        $ratios = [];
        if ($sum == 0) {
            $ratios = [0, 0, 0, 0, 0, 0, 0];
        } else {
            foreach ($sales as $sale) {
                $ratios[] = $sale / $sum * 100;
            }
        }
        return $this->success([
            'total' => $sum,
            'ratios' => $ratios
        ]);
    }

    public function getSalesToday(): JsonResponse
    {
        $sales = Cart::query()
            ->where('status', '!=', 1)
            ->whereDate('created_at', '=', date('y-m-d', strtotime('today')))
            ->get();
        $total = 0;
        foreach ($sales as $sale) {
            $total += $sale['total'];
        }
        return $this->success([
            'total' => $total
        ]);
    }

    public function getOrdersOnCart(Cart $cart): JsonResponse
    {
        return $this->success(OrderResource::collection($cart['orders']));
    }


    public function userReport(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date']
        ]);
        $carts = auth()->user()->carts()->whereDate('created_at', '>=', $request['date'])->with('orders')->latest()->get();
        if (!count($carts)) {
            return $this->failed('There are not orders in this period', 404);
        }
        $pdf = Pdf::loadView('userReport', compact('carts'));
        $pdf->setPaper('A4');
        $pdf->getOptions()->set([
            'defaultFont' => 'Helvetica',
        ]);
        require_once(base_path('vendor/autoload.php'));
        $ultramsg_token = env('WHATSAPP_TOKEN'); // Ultramsg.com token
        $instance_id = env('WHATSAPP_ID'); // Ultramsg.com instance id
        $client = new WhatsAppApi($ultramsg_token, $instance_id);
        $number = "+963" . substr(auth()->user()['phone_number'], 1, 9);
        $to = $number;
        $filename = "Report.pdf";
        $caption = "Report from " . Carbon::parse($request['date'])->diffForHumans();;
        $document = base64_encode($pdf->output());

        $client->sendDocumentMessage($to, $filename, $document, $caption, "",);
        return $this->success(null, 'we send the pdf');

    }

    public function adminReport(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date']
        ]);
        $users = User::query()->whereHas('carts', function (Builder $query) use ($request) {
            $query->whereDate('created_at', '>=', $request['date'])->with('orders')->latest();
        })->get();
        if (!count($users)) {
            return $this->failed('There are not orders in this period', 404);
        }
        $pdf = Pdf::loadView('adminReport', compact('users'));
        $pdf->setPaper('A4');
        $pdf->getOptions()->set([
            'defaultFont' => 'Helvetica',
        ]);
        require_once(base_path('vendor/autoload.php'));
        $ultramsg_token = env('WHATSAPP_TOKEN'); // Ultramsg.com token
        $instance_id = env('WHATSAPP_ID'); // Ultramsg.com instance id
        $client = new WhatsAppApi($ultramsg_token, $instance_id);
        $number = "+963" . substr(auth()->user()['phone_number'], 1, 9);
        $to = $number;
        $filename = "Report.pdf";
        $caption = "Report from " . Carbon::parse($request['date'])->diffForHumans();;
        $document = base64_encode($pdf->output());

        $client->sendDocumentMessage($to, $filename, $document, $caption);
        return $this->success(null, 'we send the pdf');

    }
}
