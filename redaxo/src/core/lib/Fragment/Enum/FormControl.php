<?php

namespace Redaxo\Core\Fragment\Enum\FormControl;

enum Autocapitalize: string
{
    case Characters = 'characters';
    case None = 'none';
    case Off = 'off';
    case On = 'on';
    case Sentences = 'sentences';
    case Words = 'words';
}
