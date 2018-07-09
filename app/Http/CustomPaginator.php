<?php namespace  App\Http;

class CustomPaginator extends \Illuminate\Pagination\LengthAwarePaginator {
  public function setPerPage($perPage) {
    $this->per_page = $perPage;
  }

  public function setCurrentPage($currentPage, $pageName) {
    $this->current_page = $currentPage;
  }
}