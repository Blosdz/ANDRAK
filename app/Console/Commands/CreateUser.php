<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    protected $signature = 'andrak:user-create';

    protected $description = 'Crea un usuario de acceso sin mostrar la contraseña';

    public function handle(): int
    {
        $nombre = $this->ask('Nombre completo');
        $mail = $this->ask('Correo electrónico');
        $password = $this->secret('Contraseña (mínimo 8 caracteres)');

        $validation = Validator::make(compact('nombre', 'mail', 'password'), [
            'nombre' => ['required', 'string', 'max:255'],
            'mail' => ['required', 'email', 'max:255', 'unique:usuarios,mail'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validation->fails()) {
            $this->error($validation->errors()->first());

            return self::FAILURE;
        }

        Usuario::create($validation->validated());
        $this->info('Usuario creado.');

        return self::SUCCESS;
    }
}
