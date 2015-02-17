<?php

namespace Cowb\Content;

use Bolt\Content;

class VideoContent extends Content
{
    public function getYoutubeId()
    {
        $url = $this->get('url');
        if (preg_match('/v=([A-Za-z0-9\-\_]+)/', $url, $matches)) {
            return $matches[1];
        }
    }
    
    public function getThumbUrl() {
        return 'https://i.ytimg.com/vi/' . $this->getYoutubeId() . '/mqdefault.jpg';
    }
}