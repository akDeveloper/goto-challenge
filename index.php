<?php

declare(strict_types=1);

try {
    $method = filter_input(INPUT_SERVER, "REQUEST_METHOD");
    $path = filter_input(INPUT_SERVER, "REQUEST_URI");

    if ($path === '/' && $method === "GET") {
        goto home;
    } elseif (preg_match("/\/([a-z]+)$/", $path, $args) && $method === "GET") {
        $brand = $args[1];
        goto generate_card;
    } elseif (preg_match("/\/([a-z]+)\/([0-9]+)$/", $path, $args) && $method === "GET") {
        $brand = $args[1];
        $bin = $args[2];
        goto generate_card;
    } else {
        throw new InvalidArgumentException("Requested path {$path} not found.", 404);
    }

    home:
    $result = [
        "title" => "credit card generator",
        "links" => [
            [
                "href" => "/visa",
                "rel" => "visa",
                "type" => "GET"
            ],
            [
                "href" => "/master",
                "rel" => "mastercard",
                "type" => "GET"
            ],
            [
                "href" => "/amex",
                "rel" => "american express",
                "type" => "GET"
            ],
            [
                "href" => "/union",
                "rel" => "union pay",
                "type" => "GET"
            ],
        ]
    ];
    goto return_response;

    generate_card:
    switch ($brand) {
        case "visa":
            $bin = $bin ?? \random_int(400000, 499999);
            $length = 16;
            break;
        case "master":
            $bin = $bin ?? random_int(5100, 5599);
            $brand = "mastercard";
            $length = 16;
            break;
        case "amex":
            $prefix = ["34", "37"];
            $bin = $bin ?? $prefix[random_int(0 ,1)];
            $brand = "american express";
            $length = 15;
            break;
        case "union":
            $bin = "62";
            $length = [13, 16][random_int(0, 1)];
            $brand = "union pay";
            break;
        default:
            throw new InvalidArgumentException("Not a valid card schema", 404);
            break;
    }
    $ccnumber = strval($bin);

    while (strlen($ccnumber) < ($length - 1)) {
        $ccnumber .= rand(0, 9);
    }

    # Calculate sum
    $sum = 0;
    $pos = 0;
    $reversedCCnumber = strrev($ccnumber);

    while ($pos < $length - 1) {
        $odd = $reversedCCnumber[ $pos ] * 2;
        if ($odd > 9) {
            $odd -= 9;
        }
        $sum += $odd;

        if ($pos != ($length - 2)) {
            $sum += $reversedCCnumber[ $pos +1 ];
        }
        $pos += 2;
    }

    # Calculate check digit
    $checkdigit = ((floor($sum/10) + 1) * 10 - $sum) % 10;

    $number = sprintf("%s%s", $ccnumber, $checkdigit);
    $result = ['brand' => $brand, 'number' => $number];
    goto return_response;

} catch (Exception $e) {
    $result = ["error" => $e->getCode(), "message" => $e->getMessage()];
    header("HTTP/1.1 404 Not Found");
    goto return_response;
}

return_response:
header('Content-Type: appliation.json');
echo json_encode($result);
exit(0);