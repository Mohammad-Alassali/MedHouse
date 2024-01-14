<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Report</title>
    <style>
        @page {
            margin: 0in;
        }

        .page-break {
            page-break-after: always;
        }

        body {
            background-color: rgba(214, 235, 252, 255);
        }

        .styled-table {

            border-collapse: collapse;
            font-size: 0.9em;
            font-family: Calibri;
            min-width: 400px;
            border-radius: 5px 5px 0 0;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            text-align: center;
            margin-left: 16%;
            width: 560px;
        }

        .styled-table thead tr {
            font-weight: bold;
            font-family: Calibri1;
            background-color: rgba(3, 168, 244, 185);
            color: #ffffff;
        }

        .styled-table th,
        .styled-table td {
            padding: 15px 15px;
        }

        .styled-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }

        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }

        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid rgba(3, 168, 244, 185)
        }

        .styled-table tbody tr.active-row {
            font-family: Calibri1;
            color: rgba(3, 168, 244, 185);
        }

    </style>

</head>
<body>
<div>
    <div style="margin-left: 348px; margin-top: 10px ">
        <img width="100px" height=100px"" src="{{public_path('/Logo/pills.png')}}">
    </div>
    <div>
        <p style="font-family:Calibri1; margin-left: 349px; font-size: x-large ">MedHouse</p>
    </div>
    <div>
        @foreach($carts as $cart)
            <p style="margin-left: 16%; font-family: Calibri ;font-size: large; text-decoration: underline;">{{$cart->created_at->format('d/m/Y')}}
                :</p>
            <table class="styled-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Scientific Name</th>
                    <th>Commercial Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $quantity=0;
                    if(auth()->user()->lang=='ar'){
                        auth()->user()->lang='en';
                    }
                @endphp
                @foreach($cart->orders as $order)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$order->product->scientific_name}}</td>
                        <td>{{$order->product->commercial_name}}</td>
                        <td>{{number_format($order->product->price)}}</td>
                        <td>{{$order->quantity}}</td>
                        <td>{{number_format($order->total)}}</td>
                    </tr>
                    @php
                        $quantity+=$order->quantity;
                    @endphp
                @endforeach
                <tr class="active-row">
                    <td colspan="2">Order Number : {{$cart->number}}</td>
                    <td colspan="2">Quantity : {{$quantity}}</td>
                    <td colspan="2">Total : {{number_format($cart->total)}}</td>
                </tr>
                </tbody>
            </table>
            <br>
        @endforeach
    </div>
</div>
</body>
</html>
