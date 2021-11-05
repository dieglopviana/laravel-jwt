<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## Autenticação JWT com Laravel 8 e PHP 8

Inicie uma aplicação laravel com o comando:

> ```composer create-project laravel/laravel your_app_name```

Isso criará uma aplicação laravel do zero. Após esse comando, a página welcome do Laravel já deve ser exibida, agora vamos rodar o camando para rodar as migrations:

> ```php artisan migrate```

Por default, o Laravel já cria o Model User.php e a tabela users, porém, sem usuários, portanto, vamos criar o controller UserController.php para gerenciar estes usuários:

> ```php artisan make:controller UserController --api```

Este comando criará um controller com métodos para uma aplicação de API. Vamos alterar o método index() do arquivo UserController.php:

```
public function index()
{
    return response()->json(User::all());
}
```

Vamos adicionar a rota no arquivo /routes/api.php e acessar pelo Postman, lembrando que por padrão, rotas api no Laravel, tem o prefixo "api", portanto a rota abaixo seria acessível em "http://your-local-domain/api/users"

```
Route::get('/users', [UserController::class, 'index']);
```

![image](https://user-images.githubusercontent.com/456792/140555197-9a60f613-efbc-4439-85ce-83d13e83e284.png)

Isso deverá retornar uma lista vazia, pois ainda não temos usuários, portanto, vamos implementar o método store() do controller UserController.php, deixando assim:

```
public function store(Request $request)
{
    return User::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password'))
    ]);
}
```

E devemos também, incluir na rota, se atentando que agora o método dela é POST, pois vamos receber os dados para o cadastro do usuário, que são o "name", o "email" e o "password", estes campos já são suficientes para uma autenticação posteriormente.

```
Route::post('/users', [UserController::class, 'store']);
```

Vamos acessar esta rota, passando os parêmetros para cadastro do usuário:

![image](https://user-images.githubusercontent.com/456792/140556181-132b4102-0ea4-4290-8bc6-99537679d22d.png)

Agora já temos um usuário cadastrado, já podemos implementar a autenticação com JWT, para isso, segue os passos necessários:


>O passo a passo abaixo, eu implementei usando como base o tutorial do Paulo Roberto Bolsanello, no link https://paulorb.net/autenticacao-jwt-laravel-8-direto-ao-ponto/. Tive que fazer algumas adaptações em alguns erros que tive, mas foram bem poucos, excelente material, coloquei o conteúdo do tutorial abaixo, caso por algum motivo, o link venha quebrar


Passo 1: Instalar o componente jwt-auth via composer:

>```composer require tymon/jwt-auth:"dev-develop"```

Passo 2: Adicionar o service provider no arquivo config/app.php

```
'providers' => [
    ... 
    Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
]
```

Passo 3: Execute o seguinte comando para publicar o arquivo de configuração do pacote:

>```php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"```

Passo 4: Gerar chave secreta

>```php artisan jwt:secret```

após a execução do comando o arquivo .env do seu projeto será atualizado e no final dele haverá o seguinte código

```
JWT_SECRET=valordasuachavegeradopelocomponentejwt
```

Passo 5: Deixe seu model igual o "./app/Models/User.php"

Passo 6: Editar o arquivo "./config/auth.php"

```
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
        'hash' => false,
    ],
],
```

Passo 7: Criar o middleware;

Middleware é um arquivo que executa determinados comandos antes ou depois de requisições e assim auxilia na verificação de acesso as rotas.

>```php artisan make:middleware apiProtectedRoute```

Altere o arquivo para ficar igual o "app/Http/Middleware/apiProtectedRoute.php"

O Código acima serve para validar as requisições feitas as rotas que serão protegidas pelo JWT, caso o Token enviado seja inválido, vazio ou expirado o Middleware apresenta uma mensagem de retorno apropriada para cadas situação.
Depois que terminar de preparar o middleware você deverá abrir o arquivo kernel.php que está na pasta app\Http e acrescenta-lo ao método $routeMiddleware.

```
protected $routeMiddleware = [
    ...
    'apiJWT' => \App\Http\Middleware\apiProtectedRoute::class,
];
```

Passo 8: Criar o AuthController;

>```php artisan make:controller AuthController```

Deixar o arquivo conforme o "app/Http/Controllers/AuthController.php".

Passo 9: Criar as rotas básicas;

```
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('/users', [UserController::class, 'store']);

Route::middleware(['apiJWT'])->group(function () {
    /** Informações do usuário logado */
    Route::get('auth/me', [AuthController::class, 'me']);

    /** Encerra o acesso */
    Route::get('auth/logout', [AuthController::class, 'logout']);

    /** Atualiza o token */
    Route::get('auth/refresh', [AuthController::class, 'refresh']);
    /** Listagem dos usuarios cadastrados, este rota serve de teste para verificar a proteção feita pelo jwt */

    Route::get('/users', [UserController::class, 'index']);
    /*Daqui para baixo você pode ir adiciondo todas as rotas que deverão estar protegidas em sua API*/
});
```

Agora vamos acessar a rota de login passando o email e senha do usuários que salvamos mais acima:

![image](https://user-images.githubusercontent.com/456792/140559652-1d93c500-38f6-46ae-ac61-e78ca1564969.png)

Agora temos um token e nossa rota que listava os usuários agora está protegida, só é possível acessá-la se passarmos o token, como mostra as images:

Tentando listar os usuários sem passar o token:
![image](https://user-images.githubusercontent.com/456792/140560015-26deda50-3572-4bd2-8f59-0038502b7128.png)

Listanto os usuários passando o token:
![image](https://user-images.githubusercontent.com/456792/140560273-f7793464-76bb-4f09-aa35-295e8446dce3.png)

E é isso, espero que funcione tudo conforme o passo a passo aqui. Abraço!!!
