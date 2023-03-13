<?php
class GroupBadgePart
{
    public const BASE = 'b';
    public const SYMBOL = 's';
    public const SYMBOL_ALT = 't';
    public const BASE_PART = 0;
    public const LAYER_PART = 1;
    public const IMAGE_WIDTH = 39;
    public const IMAGE_HEIGHT = 39;
    public const CELL_WIDTH = 13;
    public const CELL_HEIGHT = 13;

    public $type;
    public $key;
    public $color;
    public $position;

    public function __construct($type, $key = 0, $color = 0, $position = 0)
    {
        $this->type = $type;
        $this->key = intval($key);
        $this->color = intval($color);
        $this->position = intval($position);
    }

    public function code(): string
    {
        if ($this->key == 0) return null;

        return static::getCode($this->type, $this->key, $this->color, $this->position);
    }

    public static function getCode($type, $key, $color, $position): string
    {
        return $type == static::BASE
            ? $type
            : ($key >= 100
                ? static::SYMBOL_ALT
                : static::SYMBOL + ($key < 10
                    ? '0'
                    : '' + ($type == static::BASE
                        ? $key
                        : ($key >= 100
                            ? $key - 100
                            : $key + ($color < 10
                                ? '0'
                                : '' + $color + $position
                            ))
                    )
                )
            );
    }

    public function calculatePosition($asset)
    {
        $gridPos = $this->calculateGridPos($this->position);

        $imgWidth = imagesx($asset);
        $imgHeight = imagesy($asset);

        $x = (((static::CELL_WIDTH * $gridPos['x']) + (static::CELL_WIDTH / 2)) - ($imgWidth / 2));
        $y = (((static::CELL_HEIGHT * $gridPos['y']) + (static::CELL_HEIGHT / 2)) - ($imgHeight / 2));

        if ($x < 0) $x = 0;

        if (($x + $imgWidth) > static::IMAGE_WIDTH) $x = (static::IMAGE_WIDTH - $imgWidth);

        if ($y < 0) $y = 0;

        if (($y + $imgHeight) > static::IMAGE_HEIGHT) $y = (static::IMAGE_HEIGHT - $imgHeight);

        return ["x" => floor($x), "y" => floor($y)];
    }

    public static function colorize(&$im, $replace)
    {
        array_walk($replace, function (&$v, $k) {
            $v /= 255;
        });
        for ($x = 0; $x < imagesx($im); $x++) {
            for ($y = 0; $y < imagesy($im); $y++) {
                $color = imagecolorsforindex($im, imagecolorat($im, $x, $y));

                $r = $color["red"] * $replace[0];
                $g = $color["green"] * $replace[1];
                $b = $color["blue"] * $replace[2];
                $a = $color["alpha"];
                $newcolour = imagecolorallocatealpha($im, $r, $g, $b, $a);
                if ($newcolour === false) {
                    $newcolour = imagecolorclosestalpha($im, $r, $g, $b, $a);
                }
                imagesetpixel($im, $x, $y, $newcolour);
            }
        }
    }

    public static function extractParts($data)
    {
        $result = array();
        if (strlen($data) >= 6) {
            $result[] = substr($data, 0, 3);
            $result[] = substr($data, 3, 2);
            $result[] = substr($data, strlen($data) - 1);
        } else {
            $result = str_split($data, 2);
        }

        return $result;
    }

    private function calculateGridPos($gridVal)
    {
        return ["x" => floor(($gridVal % 3)), "y" => floor(($gridVal / 3))];
    }
}
