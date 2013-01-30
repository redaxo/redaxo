<?php

namespace Netcarver\Textile;

/**
 * Copyright (c) 2012 Netcarver http://github.com/netcarver
 * _____________
 * L I C E N S E
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * * Neither the name Textile nor the names of its contributors may be used to
 * endorse or promote products derived from this software without specific
 * prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 **/


/**
 * Class to allow simple assignment to members of the internal data array
 **/
class DataBag
{
    protected $data;


    public function __construct($initial_data)
    {
        $this->data = (is_array($initial_data)) ? $initial_data : array();
    }


    /**
     * Allows setting of an element in the $data array. eg...
     *
     * $bag->key(value);
     *
     * ...sets $bag's $data['key'] to $value provided $value is not empty.
     * The set can be made forced by following $value with true...
     *
     * $bag->key(value, true);
     *
     * Would force the value into the data array even if it were empty.
     **/
    public function __call($k, $params)
    {
        $allow_empty = isset($params[1]) && is_bool($params[1]) ? $params[1] : false;
        if ($allow_empty || '' != $params[0]) {
            $this->data[$k] = $params[0];
        }

        return $this;
    }
}
