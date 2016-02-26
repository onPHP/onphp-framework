<?php

/**
 * Class SimpleApplicationUrl
 */
class SimpleApplicationUrl extends ApplicationUrl
{
    /**
     * @param $requestUri
     * @param bool|true $normalize
     * @return ApplicationUrl|void
     * @throws UnimplementedFeatureException
     */
    public function setPathByRequestUri($requestUri, $normalize = true)
    {
        throw new UnimplementedFeatureException(__CLASS__ . '::setPathByRequestUri');
    }

    public function href($url, $absolute = null)
    {
        if ($absolute === null)
            $absolute = $this->absolute;

        $baseUrl = $this->getBase()->getPath() . $url;

        if ($this->applicationScope)
            $baseUrl .=
                $this->getArgSeparator()
                . $this->buildQuery($this->applicationScope);

        if ($absolute)
            $baseUrl =
                'http:' . $this->getBase()->getSchemeSpecificPart()
                . ltrim($baseUrl, '/');


        return rtrim($baseUrl, '?');
    }
}