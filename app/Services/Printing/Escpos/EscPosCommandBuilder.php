<?php

namespace App\Services\Printing\Escpos;

final class EscPosCommandBuilder
{
    private array $parts = [];

    /**
     * Initialize printer and explicitly force normal orientation/state.
     */
    public function init(): self
{
    // ESC @
    // Initialize printer
    $this->command("\x1B\x40");

    // ESC { 0
    // Turn OFF upside-down printing
    $this->command("\x1B\x7B\x00");

    // GS B 0
    // Turn OFF reverse/inverted printing
    $this->command("\x1D\x42\x00");

    // ESC V 0
    // Turn OFF 90-degree rotated character mode
    $this->command("\x1B\x56\x00");

    // Normal text size
    $this->size('normal');

    // Bold OFF
    $this->bold(false);

    // Underline OFF
    $this->underline(false);

    // Default line spacing
    $this->lineSpacing();

    return $this;
}

    /**
     * Text alignment.
     */
    public function align(string $v): self
    {
        $m = [
            'left'   => 0,
            'center' => 1,
            'right'  => 2,
        ][$v] ?? 0;

        return $this->command("\x1B\x61" . chr($m));
    }

    /**
     * Bold ON/OFF.
     */
    public function bold(bool $on = true): self
    {
        return $this->command(
            "\x1B\x45" . chr($on ? 1 : 0)
        );
    }

    /**
     * Underline ON/OFF.
     */
    public function underline(bool $on = true): self
    {
        return $this->command(
            "\x1B\x2D" . chr($on ? 1 : 0)
        );
    }

    /**
     * Text size.
     */
    public function size(string $preset = 'normal'): self
    {
        $m = [
            'normal' => 0x00,
            'double' => 0x11,
            'large'  => 0x11,
            'wide'   => 0x10,
            'tall'   => 0x01,
        ][$preset] ?? 0x00;

        return $this->command(
            "\x1D\x21" . chr($m)
        );
    }

    /**
     * Line spacing.
     */
    public function lineSpacing(?int $dots = null): self
    {
        if ($dots === null) {
            // ESC 2
            return $this->command("\x1B\x32");
        }

        return $this->command(
            "\x1B\x33" . chr(
                max(0, min(255, $dots))
            )
        );
    }

    /**
     * Feed paper.
     */
    public function feed(int $lines = 1): self
    {
        return $this->command(
            "\x1B\x64" . chr(
                max(0, min(255, $lines))
            )
        );
    }

    /**
     * Cut paper.
     */
    public function cut(bool $partial = false): self
    {
        return $this->command(
            "\x1D\x56" . chr($partial ? 1 : 0)
        );
    }

    /**
     * Add text.
     */
    public function text(string $text): self
    {
        $this->parts[] = [
            'type' => 'text',
            'data' => $text,
        ];

        return $this;
    }

    /**
     * Add newline(s).
     */
    public function newline(int $count = 1): self
    {
        for ($i = 0; $i < $count; $i++) {
            $this->parts[] = [
                'type' => 'newline',
                'data' => "\n",
            ];
        }

        return $this;
    }

    /**
     * Add raw ESC/POS command.
     */
    public function command(string $bytes): self
    {
        $this->parts[] = [
            'type' => 'command',
            'data' => $bytes,
        ];

        return $this;
    }

    /**
     * Convert all commands/text into final printer bytes.
     */
    public function toBytes(): string
    {
        return implode(
            '',
            array_column($this->parts, 'data')
        );
    }

    /**
     * Human-readable debug representation.
     */
    public function toDebugString(): string
    {
        $out = '';

        foreach ($this->parts as $p) {

            if ($p['type'] === 'command') {

                $out .= '[';

                $out .= implode(
                    ' ',
                    array_map(
                        fn($c) => strtoupper(
                            str_pad(
                                dechex(ord($c)),
                                2,
                                '0',
                                STR_PAD_LEFT
                            )
                        ),
                        str_split($p['data'])
                    )
                );

                $out .= ']';

            } else {

                $out .= $p['data'];
            }
        }

        return $out;
    }
}