<?php

namespace AdamTyn\Lumen\Artisan;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Console\ConfirmableTrait;

class KeyGenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:generate
                    {--show : Display the key instead of modifying files}
                    {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application key';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            $this->line('<comment>' . $key . '</comment>');
            return ;
        }

        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using a
        // secure random byte generator and is later base64 encoded for storage.
        if (!$this->setKeyInEnvironmentFile($key)) {
            return;
        }

        $this->laravel['config']['app.key'] = $key;

        $this->info("Application key [$key] set successfully.");
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function generateRandomKey()
    {
        return base64_encode(
                Encrypter::generateKey($this->laravel['config']['app.cipher'])
            );
    }

    /**
     * Set the application key in the environment file.
     *
     * @param string $key
     * @return bool
     */
    protected function setKeyInEnvironmentFile($key)
    {
        $currentKey = $this->laravel['config']['app.key'];

        if (strlen($currentKey) !== 0 && (!$this->confirmToProceed())) {
            return false;
        }

        $this->writeNewEnvironmentFileWith($key);

        return true;
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param string $key
     * @return void
     */
    protected function writeNewEnvironmentFileWith($key)
    {
        file_put_contents($this->environmentFilePath(), preg_replace(
            $this->keyReplacementPattern(),
            'APP_KEY=' . $key,
            file_get_contents($this->environmentFilePath())
        ));
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern()
    {
        $escaped = preg_quote('=' . env('APP_KEY'), '/');

        return "/^APP_KEY{$escaped}/m";
    }

    /**
     * @author AdamTyn
     * @description 获取env文件的完整路径
     *
     * @return string
     */
    private function environmentFilePath()
    {
        $divide = DIRECTORY_SEPARATOR;
        $path = __DIR__;
        $temp = explode($divide, $path);
        $basePath = '';
        $count = count($temp);
        for ($i = 0; $i < $count - 4; ++$i) {
            $basePath .= ($temp[$i] . $divide);
        }
        return $basePath . '.env';
    }
}
