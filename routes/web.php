<?php



Route::get("/", function() {
    $matrixAlgorithmService = new \Dev\Domain\Service\MatrixAlgorithmService();
    return $matrixAlgorithmService->encrypt('a');
});