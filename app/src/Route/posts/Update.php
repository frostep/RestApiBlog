<?php

declare(strict_types=1);

namespace Alex\RestApiBlog\Route\posts;

use Alex\RestApiBlog\Models\PostMapper;
use Alex\RestApiBlog\validation\Validator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Update
{
    public function __construct(private PostMapper $post, private Validator $validator)
    {
        $this->post = $post;
        $this->validator = $validator;
    }

    public function __invoke(Request $request, Response $response, $args): Response
    {
        // инЪекция
        $requestData = array_map(fn ($val) => htmlspecialchars(strip_tags($val)), $request->getParsedBody());
        // $requestData = array_filter($requestData, fn ($key) => '_METHOD' !== $key, ARRAY_FILTER_USE_KEY);
        $url_key = htmlspecialchars($args['url_key']);

        // проверяем
        $err = $this->validator->validate($requestData);

        // если ошибок нет
        if (!$err) {
            $this->post->update($requestData, $url_key);
            $out = json_encode(
                [
                    'status' => true,
                    'message' => "Пост {$requestData['title']} успешно обновлен",
                ],
                JSON_PRETTY_PRINT,
            );
        } else {
            $out = json_encode(
                [
                    'status' => false,
                    'message' => implode('', array_values($err)), ],
                JSON_PRETTY_PRINT,
            );
        }

        $response->getBody()->write($out);

        return $response->withHeader('Content-Type', 'application\json');
    }
}
