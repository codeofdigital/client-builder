<?php

namespace CodeOfDigital\ClientBuilder\Enums;

enum HttpMethod
{
    case HEAD;
    case GET;
    case POST;
    case PUT;
    case PATCH;
    case DELETE;
    case OPTIONS;
}