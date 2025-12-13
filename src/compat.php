<?php

declare(strict_types=1);

// @phpstan-ignore-next-line
if (!is_callable('getmypid')) {
    function getmypid(): int
    {
        try {
            return random_int(1, 32768);
        } catch (Throwable) {
            return (int) (microtime(true) * 1000) % 32768 ?: 1;
        }
    }
}

// @phpstan-ignore-next-line
if (!is_callable('gethostname')) {
    function gethostname(): string
    {
        return substr(str_shuffle('abcdefghjkmnpqrstvwxyz0123456789'), 0, 32);
    }
}
