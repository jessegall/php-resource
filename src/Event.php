<?php

namespace JesseGall\Resources;

enum Event: string
{

    case INITIALIZED = 'initialized';
    case HYDRATED = 'hydrated';
    case SAVED = 'saved';

}
