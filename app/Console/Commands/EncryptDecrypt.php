<?php

namespace App\Console\Commands;

use Dev\Domain\Service\MatrixAlgorithmService;
use Dev\Domain\Service\ReverseEncryptionAlgorithmService;
use Dev\Domain\Service\ShiftAlgorithmService;
use Illuminate\Console\Command;


class EncryptDecrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EncryptDecrypt';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $algorithm = $this->choice(
            "Select Algorithm",
            ['Shift Algorithm', 'Matrix Algorithm', 'Reverse Algorithm']
        );

        $method = $this->choice(
            "Select the method",
            ['Encrypt', 'Decrypt']
        );

        $string = $this->ask(
            "String to be encrypted / decrypted"
        );

        if ($algorithm == 'Shift Algorithm') {
            $shiftAlgorithmService = new ShiftAlgorithmService();
            ($method == 'Encrypt') ? $result = $shiftAlgorithmService->encrypt($string) : $result = $shiftAlgorithmService->decrypt($string);

        } elseif ($algorithm == 'Matrix Algorithm') {

            $matrixAlgorithmService = new MatrixAlgorithmService();
            ($method == 'Encrypt') ? $result = $matrixAlgorithmService->encrypt($string) : $result = $matrixAlgorithmService->decrypt($string);
        } elseif ($algorithm == 'Reverse Algorithm') {
            $reverseEncryptionAlgorithmService = new ReverseEncryptionAlgorithmService();
            ($method == 'Encrypt') ? $result = ($reverseEncryptionAlgorithmService->encrypt($string))  : $result = ($reverseEncryptionAlgorithmService->decrypt($string));
        }

        $this->info("The result is '{$result}'");
    }
}