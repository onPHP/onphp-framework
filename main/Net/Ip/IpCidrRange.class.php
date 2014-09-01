<?php
/**
 * Class to work with cidr ranges
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2013.12.02
 */

/**
 * @ingroup Ip
 */

class IpCidrRange implements SingleRange, DialectString, Stringable {

    /** @var IpAddress */
    protected $startIp;

    /** @var IpAddress */
    protected $endIp;

    protected $mask;

    /**
     * @return IpCidrRange
     **/
    public static function create($string)
    {
        return new static($string);
    }

    public function __construct( $string ) {
        Assert::isString($string);
        if ( preg_match( IpRange::SINGLE_IP_PATTERN, $string ) ) {
            $ip = IpAddress::create($string);
            $this->startIp = $ip;
            $this->endIp = $ip;
            $this->mask = 32;
        } elseif ( preg_match( IpRange::IP_SLASH_PATTERN, $string ) ) {
            list($ip, $mask) = explode('/', $string);
            $this->createFromSlash($ip, $mask);
        } elseif ( preg_match( IpRange::IP_WILDCARD_PATTERN, $string ) ) {
            $ip = substr($string, 0, -2);
            $mask = substr_count($string, '.') * 8;
            $this->createFromSlash($ip, $mask);

        } else {
            throw new WrongArgumentException('strange parameters received');
        }
    }

    protected function createFromSlash($ip, $mask)
    {
        $ip = IpAddress::createFromCutted($ip);

        if($mask == 32) {
            $this->startIp = $this->endIp = $ip;
            $this->mask = $mask;
            return;
        }

        if ($mask == 0 || IpRange::MASK_MAX_SIZE < $mask)
            throw new WrongArgumentException('wrong mask given');

        $longMask =
            (int) (pow(2, (32 - $mask)) * (pow(2, $mask) - 1));

        if (($ip->getLongIp() & $longMask) != $ip->getLongIp())
            throw new WrongArgumentException('wrong ip network given');

        $this->startIp = $ip;
        $this->endIp = IpAddress::create( long2ip($ip->getLongIp() | ~$longMask) );
        $this->mask = $mask;
    }

    public function toString() {
        return $this->getStart()->toString() . '/' . $this->getMask();
    }

    public function toDialectString(Dialect $dialect) {
        return $dialect->quoteValue($this->toString());
    }

    /**
     * @return IpAddress
     **/
    public function getStart()
    {
        return $this->startIp;
    }

    /**
     * @return IpAddress
     **/
    public function getEnd()
    {
        return $this->endIp;
    }

    public function getMask() {
        return $this->mask;
    }

    public function contains(/* IpAddress */ $probe) {
        /** @var IpAddress $probe */
        Assert::isInstance($probe, 'IpAddress');

        return (
            ($this->startIp->getLongIp() <= $probe->getLongIp())
            && ($this->endIp->getLongIp() >= $probe->getLongIp())
        );
    }

} 