<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    /**
     * return all companies
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->success(CompanyResource::collection(Company::all()), '');
    }


    public function show($id)
    {
        $company = Company::query()->find($id);
        if (!$company) {
            return $this->failed('company not found', 404);
        }
        return $this->success(CompanyResource::make($company));
    }

    /**
     * store new company by super admin only
     * @param StoreCompanyRequest $request
     * @return JsonResponse
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['icon'] = ImageController::store($request->file('icon'), "Companies");
        return $this->success(
            CompanyResource::make(Company::query()->create($data))
            , ''
        );
    }


    /**
     * update name or company of company by super admin
     * @param UpdateCompanyRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(UpdateCompanyRequest $request, $id): JsonResponse
    {
        $request->validated();
        $company = Company::query()->find($id);
        if (!$company) {
            return $this->failed('company not found', 404);
        }
        if ($request->has('name')) {
            $company->update([
                'name' => $request['name']
            ]);
        }
        if ($request->has('icon')) {
            $iconName = ImageController::update($request->file('icon'), $company['icon'], "Companies");
            $company->update([
                'icon' => $iconName
            ]);

        }
        return $this->success(CompanyResource::make($company), 'updated');
    }

    /**
     * delete company by super admin
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        $company = Company::query()->find($id);
        ImageController::destroy($company['icon']);
        if ($company) {
            $company->delete();
            return $this->success(null, 'deleted');
        }
        return $this->failed('company not found', 404);
    }

}
