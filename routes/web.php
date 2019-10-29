<?php



Route::get("/", function() {
    $matrixService = new \Dev\Domain\Service\MatrixAlgorithmService();
    return $matrixService->encrypt("aa");
});