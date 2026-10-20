<?php

namespace Redaxo\Core\Content;

/** Direction in which a slice is moved within its content section. */
enum SliceMoveDirection: string
{
    case Up = 'up';
    case Down = 'down';
}
