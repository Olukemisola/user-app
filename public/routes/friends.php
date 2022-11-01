<?php

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->get('/get/users', function (Request $request, Response $response) {

    $sql = "SELECT name, email, phone, username FROM friends";
    try {
        $db = new DB;
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$friends) {
            throw new Error("No users found");
        }
        $response->getBody()->write(json_encode($friends));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

$app->get('/get/user/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "SELECT name, email, phone, username FROM friends WHERE id=$id";

    try {
        $db = new DB;
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        // $stmt->bindParam(':id', $id);

        $friends = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$friends) {
            throw new Error("No users found");
        }
        $response->getBody()->write(json_encode($friends));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    } catch (Exception $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }
});

$app->post('/add/user', function (Request $request, Response $response) {
    $data = (array)json_decode($request->getBody()->getContents());
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $username = $data['username'];
    $password = $data['password'];

    $password = password_hash($password, null);
    $sql = "INSERT INTO friends (name, email, phone, username, password) VALUES (:name, :email, :phone, :username, :password)";
    try {
        $db = new DB;
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam('username', $username);
        $stmt->bindParam('password', $password);

        $result = $stmt->execute();

        unset($data['password']);

        if (!$result) {
            throw new error("error occurred while posting");
        }
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

$app->put('/update/user/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = (array)json_decode($request->getBody()->getContents());

    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $username = $data['username'];

    $sql = "UPDATE friends SET name=:name, email=:email, phone=:phone, username=:username WHERE id=:id";
    try {
        $db = new DB;
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam('username', $username);
        $stmt->bindParam('id', $id);
        $result = $stmt->execute();

        if (!$result) {
            throw new Error("Error occurred while updating");
        }
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

$app->put('/update/user/password/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $data = (array)json_decode($request->getBody()->getContents());

    $old_password = $data['oldpassword'];
    $new_password = $data['newpassword'];

    try {
        $db = new DB;
        $conn = $db->connect();

        $sql = "SELECT password FROM friends WHERE id=$id";
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $encrypted_password = $result['password'];

        // $old_password = password_hash($old_password, null);

        if (!password_verify($old_password, $encrypted_password)) {
            throw new Error("oldpassword is invalid");
        }

        $encrypted_newPassword = password_hash($new_password, null);

        $sql = "UPDATE friends SET password=:password WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':password', $encrypted_newPassword);
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();

        if (!$result) {
            throw new Error("Error occurred while updating");
        }

        $response->getBody()->write(json_encode(["message" => "successful"]));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    } catch (Throwable $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }
});


$app->delete('/delete/user/{id}', function(Request $request, Response $response){
    $id=$request->getAttribute('id');
    $sql="DELETE FROM friends WHERE id=$id";
    try{
    $db= new DB;
    $conn= $db->connect();
    $stmt= $conn->prepare($sql);
    $result= $stmt->execute();
    $response->getBody()->write(json_encode(["message"=> "delete successfully"]));
    return $response 
    ->withHeader('content-type', 'application/json')
    ->withStatus(200);
    
    }catch(PDOException $e){
 $error= [
   "message"=>$e->getMessage()

 ];
  $response->getBody()->write(json_encode($error));
  return $response
  ->withHeader('content-type', 'application/json')
  ->withStatus(500);
    }

});
// $app->delete('/delete/user/{id}', function (Request $request, Response $response, array $args) {
//     $id = $args['id'];
//     $sql = "DELETE FROM friends WHERE id= $id";

//     try {
//         $db = new DB();
//         $conn = $db->connect();

//         $stmt = $conn->prepare($sql);
//         // $stmt->bindParam(':name', $name);
//         // $stmt->bindParam(':email', $email);
//         // $stmt->bindParam(':phone', $phone);
//         // $stmt->bindParam(':username', $username);
//         // $stmt->bindParam(':id', $id); 
//         $result = $stmt->execute();

//         $output = ["message" => "delete successfully"];

//         $response->getBody()->write(json_encode($output));

//         return $response
//             ->withHeader('content-type', 'application/json')
//             ->withStatus(200);
//     } catch (PDOException $e) {
//         $error = [
//             "message" => $e->getMessage()
//         ];

//         $response->getBody()->write(json_encode($error));

//         return $response
//             ->withHeader('content-type', 'application/json')
//             ->withStatus(500);
//     }
// });

// $app->post('/test/login', function (Request $request, Response $response) {
//     $data = (array) json_decode($request->getBody()->getContents());
//     $username = $data['username'];
//     $password = $data['password'];

//     $encrypted_password = password_hash($password, null);

//     try {

//         $db = new DB;
//         $conn = $db->connect();

//         $sql = "SELECT username,password FROM friends WHERE username=:username, password=:password";

//         $stmt = $conn->query($sql);
//         $stmt->bindParam(':username', $username);
//         $stmt->bindParam(':password', $encrypted_password);

//         $data = $stmt->fetch(PDO::FETCH_ASSOC);

//         if (!$data) {
//             throw new Error("This user does not exist");
//         }
//         $payload = [
//             "id" => $data->id,
//             "email" => $data->email
//         ];

//         $key = "secretKey1234!@#$";

//         $token = JWT::encode($payload, $key, "HS512");
//         // $decoded = JWT::decode($jwt, new Key($key, 'HS512'));
//         // $decoded_array = (array) $decoded;


//         // $response->getBody()->write(json_encode(explode($token)));
//         $response->getBody()->write(json_encode(["token" => $token]));

//         return $response
//             ->withHeader('content-type', 'application/json')
//             ->withStatus(200);
//     } catch (PDOException $e) {
//         $error = [
//             "message" => $e->getMessage()
//         ];

//         $response->getBody()->write(json_encode($error));

//         return $response
//             ->withHeader('content-type', 'application/json')
//             ->withStatus(500);

//     }
// });

// })
// To format code:Alternate shift f