<?php

if (!is_callable('getmypid')) {
    function getmypid(): int
    {
        return random_int(1, 32768);
    }
}

if (!is_callable('gethostname')) {
    function gethostname()
    {
        return base_convert(bin2hex(random_bytes(8)), 16, 32);
    }
}
