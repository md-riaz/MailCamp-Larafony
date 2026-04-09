<?php

declare(strict_types=1);

namespace Larafony\Framework\Mail\Transport;

use Larafony\Framework\Mail\Exceptions\TransportError;

/**
 * SMTP socket connection wrapper.
 */
final class SmtpConnection
{
    public bool $isConnected {
        get => ! $this->closed && is_resource($this->socket) && ! feof($this->socket);
    }
    /** @var resource */
    private mixed $socket;

    private bool $closed = false;

    private function __construct(mixed $socket)
    {
        $this->socket = $socket;
    }

    public static function create(string $host, int $port, ?string $encryption = null, int $timeout = 30): self
    {
        $errno = 0;
        $errstr = '';

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'SNI_enabled' => true,
                'peer_name' => $host,
            ],
        ]);

        $remoteHost = match ($encryption) {
            'ssl' => 'ssl://' . $host,
            default => $host,
        };

        $resource = stream_socket_client(
            $remoteHost . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context,
        );

        if ($resource === false) {
            throw new TransportError(
                "Could not connect to {$remoteHost}:{$port} - [{$errno}] {$errstr}"
            );
        }

        stream_set_timeout($resource, $timeout);

        return new self($resource);
    }

    public function enableTls(string $host, int $timeout = 30): void
    {
        if (! $this->isConnected) {
            throw new TransportError('Cannot enable TLS on closed connection');
        }

        $cryptoMethod = (defined('STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT : 0)
            | (defined('STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT : 0)
            | (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT : 0)
            | (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT : 0);

        if ($cryptoMethod === 0) {
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        }

        $result = stream_socket_enable_crypto(
            $this->socket,
            true,
            $cryptoMethod
        );

        if ($result !== true) {
            $meta = stream_get_meta_data($this->socket);
            $timedOut = !empty($meta['timed_out']) ? ' timed out' : '';
            throw new TransportError("Failed to enable TLS for {$host}{$timedOut}");
        }

        stream_set_timeout($this->socket, $timeout);
    }

    public function write(string $data): void
    {
        if (! $this->isConnected) {
            throw new TransportError('Cannot write to closed connection');
        }

        fwrite($this->socket, $data);
    }

    /**
     * @param int<0, max>|null $length
     */
    public function readLine(?int $length = 515): string
    {
        if (! $this->isConnected) {
            return '';
        }

        $line = fgets($this->socket, $length);

        if ($line === false) {
            return '';
        }

        return $line;
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;

        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }
}
