# lumen-artisan-key-generate
移植Laravel的 `php artisan key:generate` [重置AppKey]指令到Lumen

# Usage
在 **'app/commands/kernel.php'** 中注册指令：
```  

protected $commands = [
	\AdamTyn\Lumen\Artisan\KeyGenerateCommand::class
];
```
