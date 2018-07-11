<?php namespace App\Http\Controllers\ApiV2;

// use App\Http\CustomPaginator as Paginator;

class BaseIRDFormController extends BaseController {
//  protected $paginator;
//  protected $perPage = 10;

//  public function __construct() {
//    $this->paginator = new Paginator;
//  }

  public function index() {
    $input = \Input::all();

//    $currentPage = $input['page'];
//
//    $this->paginator->setCurrentPage(\Input::get('page'));
//    $this->paginator->setPerPage(\Input::get('per_page'));

    $query = $this->model;
    $total = $query->count();

    // Pagination
    $query = $query->skip(\Input::get('offset',0));
    if(\Input::has('limit')) {
      $query = $query->take(\Input::get('limit'));
    }
    $data = $query->get();
    $data = $this->onDataReady($data);

    return response()->json([
      'status'=>true,
      'result'=>[
        'data'=>$data,
        'total'=>$total
      ]
    ]);
  }

  protected function onDataReady($data) {
    return $data;
  }
}