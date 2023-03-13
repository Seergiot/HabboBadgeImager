<?php

require_once dirname(__FILE__) . '/GroupBadge.php';
require_once dirname(__FILE__) . '/GroupBadgePart.php';

class BadgeImageManager
{
    private $_imageDir = '';
    private $_bases = [],
        $_symbols = [],
        $_partColors = [],
        $_colorsA = [],
        $_colorsB = [];

    public function __construct()
    {
        $this->getBadgeElements();
        $this->_imageDir = dirname(__FILE__) . '/badgeparts/';
    }

    public function getBadgeElements()
    {
        $items = fetchAll('SELECT * FROM `groups_items`');

        foreach ($items as $item) {
            $type =  $item['type'];
            $types = ['base' => '_bases', 'symbol' => '_symbols', 'color' => '_partColors', 'color2' => '_colorsA', 'color3' => '_colorsB'];

            if (!array_key_exists($type, $types)) continue;

            $tp = $types[$type];

            $this->$tp[$item['id']] = [$item['firstvalue'], $item['secondvalue']];
        }
    }

    public function getGroupBadge(string $badgeCode)
    {
        return $this->loadGroupBadge($badgeCode);
    }

    private function loadGroupBadge(string $badgeCode)
    {
        $groupBadge = new GroupBadge($badgeCode);
        preg_match_all("/[b|s][0-9]{4,6}/", $badgeCode, $partMatches);

        foreach ($partMatches[0]  as $partMatch) {
            $partCode = $partMatch;
            $partType = $partCode[0];
            list($partId, $partColor, $partPosition) = GroupBadgePart::extractParts(substr($partCode, 1));

            $part = new GroupBadgePart($partType, $partId, $partColor, $partPosition);
            $groupBadge->appendPart($part);
        }

        return $this->renderGroupBadge($groupBadge);
    }

    public function renderGroupBadge(GroupBadge $groupBadge)
    {
        $colorRgb = array('red' => 255, 'green' => 0, 'blue' => 0);
        $backgroundImg = @imagecreatetruecolor(GroupBadgePart::IMAGE_WIDTH, GroupBadgePart::IMAGE_HEIGHT);
        $color = imagecolorallocate($backgroundImg, $colorRgb['red'], $colorRgb['green'], $colorRgb['blue']);
        imagefill($backgroundImg, 0, 0, $color);
        $bg = imagecolorexact($backgroundImg, $colorRgb['red'], $colorRgb['green'], $colorRgb['blue']);
        imagecolortransparent($backgroundImg, $bg);

        foreach ($groupBadge->parts() as $part) {
            $type = ($part->type === 'b') ? '_bases' : '_symbols';
            if (!array_key_exists($part->key, $this->$type)) continue;

            $parts = $this->$type[$part->key];
            $first = true;


            foreach ($parts as $_part) {
                $isValidPart = strlen($_part) > 0;
                if (!$isValidPart) continue;

                $imgPart = @imagecreatefrompng($this->_imageDir . str_replace('.gif', '.png', "badgepart_{$_part}"));
                if (!$imgPart) {
                    continue;
                }
                $width  = imagesx($imgPart);
                $height = imagesy($imgPart);

                $position = $part->calculatePosition($imgPart);
                if ($first && array_key_exists($part->color, $this->_partColors)) {
                    $hex = '#' . $this->_partColors[$part->color][0];
                    GroupBadgePart::colorize($imgPart, sscanf($hex, "#%02x%02x%02x"));
                }
                $first = false;
                imagecopy($backgroundImg, $imgPart, $position['x'], $position['y'], 0, 0, $width, $height);
            }
        }
        return $backgroundImg;
    }
}
