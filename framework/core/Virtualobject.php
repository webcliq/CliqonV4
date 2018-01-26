<?php
/**
 * Cliq Type Hinting and Value Object Classes
 *
 * @category   Web application framework
 * @package    Cliq
 * @author     Original Author <conkascom@gmail.com>
 * @copyright  2016 Conkas cb
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 4.1.0
 * @link       http://cliqon.com
 */

interface ValueObjectInterface
{
    /**
     * Returns a object taking PHP native value(s) as argument(s).
     *
     * @return ValueObjectInterface
     */
    public static function fromNative();

    /**
     * Compare two ValueObjectInterface and tells whether they can be considered equal
     *
     * @param  ValueObjectInterface $object
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $object);

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function __toString();
}

class InvalidNativeArgumentException extends InvalidArgumentException
{
    public function __construct($value, array $allowed_types)
    {
        $this->message = sprintf('Argument "%s" is invalid. Allowed types for argument are "%s".', $value, implode(', ', $allowed_types));
    }
}

interface NumberInterface
{
    /**
     * Returns a PHP native implementation of the Number value
     *
     * @return mixed
     */
    public function toNative();
}

/**
 * Utility class for methods used all across the library
 * @package ValueObjects\Util
 */
class Util
{
    /**
     * Tells whether two objects are of the same class
     *
     * @param  object $object_a
     * @param  object $object_b
     * @return bool
     */
    public static function classEquals($object_a, $object_b)
    {
        return \get_class($object_a) === \get_class($object_b);
    }

    /**
     * Returns full namespaced class as string
     *
     * @param $object
     * @return string
     */
    public static function getClassAsString($object)
    {
        return \get_class($object);
    }
}

class StringLiteral implements ValueObjectInterface
{
    protected $value;

    /**
     * Returns a StringLiteral object given a PHP native string as parameter.
     *
     * @param  string $value
     * @return StringLiteral
     */
    public static function fromNative()
    {
        $value = func_get_arg(0);

        return new static($value);
    }

    /**
     * Returns a StringLiteral object given a PHP native string as parameter.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        if (false === \is_string($value)) {
            throw new InvalidNativeArgumentException($value, array('string'));
        }

        $this->value = $value;
    }

    /**
     * Returns the value of the string
     *
     * @return string
     */
    public function toNative()
    {
        return $this->value;
    }

    /**
     * Tells whether two string literals are equal by comparing their values
     *
     * @param  ValueObjectInterface $stringLiteral
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $stringLiteral)
    {
        if (false === Util::classEquals($this, $stringLiteral)) {
            return false;
        }

        return $this->toNative() === $stringLiteral->toNative();
    }

    /**
     * Tells whether the StringLiteral is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return \strlen($this->toNative()) == 0;
    }

    /**
     * Returns the string value itself
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toNative();
    }
}

class Date implements ValueObjectInterface
{
    /** @var Year */
    protected $year;

    /** @var Month */
    protected $month;

    /** @var MonthDay */
    protected $day;

    /**
     * Returns a new Date from native year, month and day values
     *
     * @param  int    $year
     * @param  string $month
     * @param  int    $day
     * @return Date
     */
    public static function fromNative()
    {
        $args = func_get_args();

        $year  = new Year($args[0]);
        $month = Month::fromNative($args[1]);
        $day   = new MonthDay($args[2]);

        return new static($year, $month, $day);
    }

    /**
     * Returns a new Date from a native PHP \DateTime
     *
     * @param  \DateTime $date
     * @return Date
     */
    public static function fromNativeDateTime(\DateTime $date)
    {
        $year  = \intval($date->format('Y'));
        $month = Month::fromNativeDateTime($date);
        $day   = \intval($date->format('d'));

        return new static(new Year($year), $month, new MonthDay($day));
    }

    /**
     * Returns current Date
     *
     * @return Date
     */
    public static function now()
    {
        $date = new static(Year::now(), Month::now(), MonthDay::now());

        return $date;
    }

    /**
     * Create a new Date
     *
     * @param  Year                 $year
     * @param  Month                $month
     * @param  MonthDay             $day
     * @throws InvalidDateException
     */
    public function __construct(Year $year, Month $month, MonthDay $day)
    {
        \DateTime::createFromFormat('Y-F-j', \sprintf('%d-%s-%d', $year->toNative(), $month, $day->toNative()));
        $nativeDateErrors = \DateTime::getLastErrors();

        if ($nativeDateErrors['warning_count'] > 0 || $nativeDateErrors['error_count'] > 0) {
            throw new InvalidDateException($year->toNative(), $month->toNative(), $day->toNative());
        }

        $this->year  = $year;
        $this->month = $month;
        $this->day   = $day;
    }

    /**
     * Tells whether two Date are equal by comparing their values
     *
     * @param  ValueObjectInterface $date
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $date)
    {
        if (false === Util::classEquals($this, $date)) {
            return false;
        }

        return $this->getYear()->sameValueAs($date->getYear()) &&
               $this->getMonth()->sameValueAs($date->getMonth()) &&
               $this->getDay()->sameValueAs($date->getDay());
    }

    /**
     * Get year
     *
     * @return Year
     */
    public function getYear()
    {
        return clone $this->year;
    }

    /**
     * Get month
     *
     * @return Month
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Get day
     *
     * @return MonthDay
     */
    public function getDay()
    {
        return clone $this->day;
    }

    /**
     * Returns a native PHP \DateTime version of the current Date
     *
     * @return \DateTime
     */
    public function toNativeDateTime()
    {
        $year  = $this->getYear()->toNative();
        $month = $this->getMonth()->getNumericValue();
        $day   = $this->getDay()->toNative();

        $date = new \DateTime();
        $date->setDate($year, $month, $day);
        $date->setTime(0, 0, 0);

        return $date;
    }

    /**
     * Returns date as string in format Y-n-j
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toNativeDateTime()->format('Y-n-j');
    }
}

class Real implements ValueObjectInterface, NumberInterface
{
    protected $value;

    /**
     * Returns a Real object given a PHP native float as parameter.
     *
     * @param  float  $number
     * @return static
     */
    public static function fromNative()
    {
        $value = func_get_arg(0);

        return new static($value);
    }

    /**
     * Returns a Real object given a PHP native float as parameter.
     *
     * @param float $number
     */
    public function __construct($value)
    {
        $value = \filter_var($value, FILTER_VALIDATE_FLOAT);

        if (false === $value) {
            throw new InvalidNativeArgumentException($value, array('float'));
        }

        $this->value = $value;
    }

    /**
     * Returns the native value of the real number
     *
     * @return float
     */
    public function toNative()
    {
        return $this->value;
    }

    /**
     * Tells whether two Real are equal by comparing their values
     *
     * @param  ValueObjectInterface $real
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $real)
    {
        if (false === Util::classEquals($this, $real)) {
            return false;
        }

        return $this->toNative() === $real->toNative();
    }

    /**
     * Returns the integer part of the Real number as a Integer
     *
     * @param  RoundingMode $rounding_mode Rounding mode of the conversion. Defaults to RoundingMode::HALF_UP.
     * @return Integer
     */
    public function toInteger(RoundingMode $rounding_mode = null)
    {
        if (null === $rounding_mode) {
            $rounding_mode = RoundingMode::HALF_UP();
        }

        $value        = $this->toNative();
        $integerValue = \round($value, 0, $rounding_mode->toNative());
        $integer      = new Integer($integerValue);

        return $integer;
    }

    /**
     * Returns the absolute integer part of the Real number as a Natural
     *
     * @param  RoundingMode $rounding_mode Rounding mode of the conversion. Defaults to RoundingMode::HALF_UP.
     * @return Natural
     */
    public function toNatural(RoundingMode $rounding_mode = null)
    {
        $integerValue = $this->toInteger($rounding_mode)->toNative();
        $naturalValue = \abs($integerValue);
        $natural      = new Natural($naturalValue);

        return $natural;
    }

    /**
     * Returns the string representation of the real value
     *
     * @return string
     */
    public function __toString()
    {
        return \strval($this->toNative());
    }
}

class Currency implements ValueObjectInterface
{
    /** @var BaseCurrency */
    protected $currency;

    /** @var CurrencyCode  */
    protected $code;

    /**
     * Returns a new Currency object from native string currency code
     *
     * @param  string $code Currency code
     * @return static
     */
    public static function fromNative()
    {
        $code = CurrencyCode::get(func_get_arg(0));

        return new static($code);
    }

    public function __construct(CurrencyCode $code)
    {
        $this->code     = $code;
        $this->currency = new BaseCurrency($code->toNative());
    }

    /**
     * Tells whether two Currency are equal by comparing their names
     *
     * @param  ValueObjectInterface $currency
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $currency)
    {
        if (false === Util::classEquals($this, $currency)) {
            return false;
        }

        return $this->getCode()->toNative() == $currency->getCode()->toNative();
    }

    /**
     * Returns currency code
     *
     * @return CurrencyCode
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns string representation of the currency
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getCode()->toNative();
    }
}

class Money implements ValueObjectInterface
{
    /** @var BaseMoney */
    protected $money;

    /** @var Currency */
    protected $currency;

    /**
     * Returns a Money object from native int amount and string currency code
     *
     * @param  int    $amount   Amount expressed in the smallest units of $currency (e.g. cents)
     * @param  string $currency Currency code of the money object
     * @return static
     */
    public static function fromNative()
    {
        $args = func_get_args();

        $amount   = new Integer($args[0]);
        $currency = Currency::fromNative($args[1]);

        return new static($amount, $currency);
    }

    /**
     * Returns a Money object
     *
     * @param \ValueObjects\Number\Integer $amount   Amount expressed in the smallest units of $currency (e.g. cents)
     * @param Currency                     $currency Currency of the money object
     */
    public function __construct(Integer $amount, Currency $currency)
    {
        $baseCurrency   = new BaseCurrency($currency->getCode()->toNative());
        $this->money    = new BaseMoney($amount->toNative(), $baseCurrency);
        $this->currency = $currency;
    }

    /**
     *  Tells whether two Currency are equal by comparing their amount and currency
     *
     * @param  ValueObjectInterface $money
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $money)
    {
        if (false === Util::classEquals($this, $money)) {
            return false;
        }

        return $this->getAmount()->sameValueAs($money->getAmount()) && $this->getCurrency()->sameValueAs($money->getCurrency());
    }

    /**
     * Returns money amount
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getAmount()
    {
        $amount = new Integer($this->money->getAmount());

        return $amount;
    }

    /**
     * Returns money currency
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return clone $this->currency;
    }

    /**
     * Add an integer quantity to the amount and returns a new Money object.
     * Use a negative quantity for subtraction.
     *
     * @param  \ValueObjects\Number\Integer $quantity Quantity to add
     * @return Money
     */
    public function add(Integer $quantity)
    {
        $amount = new Integer($this->getAmount()->toNative() + $quantity->toNative());
        $result = new static($amount, $this->getCurrency());

        return $result;
    }

    /**
     * Multiply the Money amount for a given number and returns a new Money object.
     * Use 0 < Real $multipler < 1 for division.
     *
     * @param  Real  $multiplier
     * @param  mixed $rounding_mode Rounding mode of the operation. Defaults to RoundingMode::HALF_UP.
     * @return Money
     */
    public function multiply(Real $multiplier, RoundingMode $rounding_mode = null)
    {
        if (null === $rounding_mode) {
            $rounding_mode = RoundingMode::HALF_UP();
        }

        $amount        = $this->getAmount()->toNative() * $multiplier->toNative();
        $roundedAmount = new Integer(round($amount, 0, $rounding_mode->toNative()));
        $result        = new static($roundedAmount, $this->getCurrency());

        return $result;
    }

    /**
     * Returns a string representation of the Money value in format "CUR AMOUNT" (e.g.: EUR 1000)
     *
     * @return string
     */
    public function __toString()
    {
        return \sprintf('%s %d', $this->getCurrency()->getCode(), $this->getAmount()->toNative());
    }
}

class NullValue implements ValueObjectInterface
{
    /**
     * @throws \BadMethodCallException
     */
    public static function fromNative()
    {
        throw new \BadMethodCallException('Cannot create a NullValue object via this method.');
    }

    /**
     * Returns a new NullValue object
     *
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Tells whether two objects are both NullValue
     * @param  ValueObjectInterface $null
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $null)
    {
        return Util::classEquals($this, $null);
    }

    /**
     * Returns a string representation of the NullValue object
     *
     * @return string
     */
    public function __toString()
    {
        return \strval(null);
    }
}

class Integer extends Real
{
    /**
     * Returns a Integer object given a PHP native int as parameter.
     *
     * @param int $value
     */
    public function __construct($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        if (false === $value) {
            throw new InvalidNativeArgumentException($value, array('int'));
        }

        parent::__construct($value);
    }

    /**
     * Tells whether two Integer are equal by comparing their values
     *
     * @param  ValueObjectInterface $integer
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $integer)
    {
        if (false === Util::classEquals($this, $integer)) {
            return false;
        }

        return $this->toNative() === $integer->toNative();
    }

    /**
     * Returns the value of the integer number
     *
     * @return int
     */
    public function toNative()
    {
        $value = parent::toNative();

        return \intval($value);
    }

    /**
     * Returns a Real with the value of the Integer
     *
     * @return Real
     */
    public function toReal()
    {
        $value = $this->toNative();
        $real  = new Real($value);

        return $real;
    }
}

interface QueryStringInterface
{
    public function toDictionary();
}

class QueryString extends StringLiteral implements QueryStringInterface
{
    /**
     * Returns a new QueryString
     *
     * @param string $value
     */
    public function __construct($value)
    {
        if (0 === \preg_match('/^\?([\w\.\-[\]~&%+]+(=([\w\.\-~&%+]+)?)?)*$/', $value)) {
            throw new InvalidNativeArgumentException($value, array('string (valid query string)'));
        }

        $this->value = $value;
    }

    /**
     * Returns a Dictionary structured representation of the query string
     *
     * @return Dictionary
     */
    public function toDictionary()
    {
        $value = \ltrim($this->toNative(), '?');
        \parse_str($value, $data);

        return Dictionary::fromNative($data);
    }
}

class Name implements ValueObjectInterface
{
    /**
     * First name
     *
     * @var \ValueObjects\StringLiteral\StringLiteral
     */
    private $firstName;

    /**
     * Middle name
     *
     * @var \ValueObjects\StringLiteral\StringLiteral
     */
    private $middleName;

    /**
     * Last name
     *
     * @var \ValueObjects\StringLiteral\StringLiteral
     */
    private $lastName;

    /**
     * Returns a Name objects form PHP native values
     *
     * @param  string $first_name
     * @param  string $middle_name
     * @param  string $last_name
     * @return Name
     */
    public static function fromNative()
    {
        $args = func_get_args();

        $firstName  = new StringLiteral($args[0]);
        $middleName = new StringLiteral($args[1]);
        $lastName   = new StringLiteral($args[2]);

        return new static($firstName, $middleName, $lastName);
    }

    /**
     * Returns a Name object
     *
     * @param StringLiteral $first_name
     * @param StringLiteral $middle_name
     * @param StringLiteral $last_name
     */
    public function __construct(StringLiteral $first_name, StringLiteral $middle_name, StringLiteral $last_name)
    {
        $this->firstName  = $first_name;
        $this->middleName = $middle_name;
        $this->lastName   = $last_name;
    }

    /**
     * Returns the first name
     *
     * @return StringLiteral
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Returns the middle name
     *
     * @return StringLiteral
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Returns the last name
     *
     * @return StringLiteral
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Returns the full name
     *
     * @return StringLiteral
     */
    public function getFullName()
    {
        $fullNameString = $this->firstName .
            ($this->middleName->isEmpty() ? '' : ' ' . $this->middleName) .
            ($this->lastName->isEmpty() ? '' : ' ' . $this->lastName);

        $fullName = new StringLiteral($fullNameString);

        return $fullName;
    }

    /**
     * Tells whether two names are equal by comparing their values
     *
     * @param  ValueObjectInterface $name
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $name)
    {
        if (false === Util::classEquals($this, $name)) {
            return false;
        }

        return $this->getFullName() == $name->getFullName();
    }

    /**
     * Returns the full name
     *
     * @return string
     */
    public function __toString()
    {
        return \strval($this->getFullName());
    }
}

class EmailAddress extends StringLiteral
{
    /**
     * Returns an EmailAddress object given a PHP native string as parameter.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $filteredValue = filter_var($value, FILTER_VALIDATE_EMAIL);

        if ($filteredValue === false) {
            throw new InvalidNativeArgumentException($value, array('string (valid email address)'));
        }

        $this->value = $filteredValue;
    }

    /**
     * Returns the local part of the email address
     *
     * @return StringLiteral
     */
    public function getLocalPart()
    {
        $parts = explode('@', $this->toNative());
        $localPart = new StringLiteral($parts[0]);

        return $localPart;
    }

    /**
     * Returns the domain part of the email address
     *
     * @return Domain
     */
    public function getDomainPart()
    {
        $parts = \explode('@', $this->toNative());
        $domain = \trim($parts[1], '[]');

        return Domain::specifyType($domain);
    }
}

class Url implements ValueObjectInterface
{
    /** @var SchemeName */
    protected $scheme;

    /** @var StringLiteral */
    protected $user;

    /** @var StringLiteral */
    protected $password;

    /** @var Domain */
    protected $domain;

    /** @var Path */
    protected $path;

    /** @var PortNumber */
    protected $port;

    /** @var QueryString */
    protected $queryString;

    /** @var FragmentIdentifier */
    protected $fragmentIdentifier;

    /**
     * Returns a new Url object from a native url string
     *
     * @param $url_string
     * @return Url
     */
    public static function fromNative()
    {
        $urlString = \func_get_arg(0);

        $user        = \parse_url($urlString, PHP_URL_USER);
        $pass        = \parse_url($urlString, PHP_URL_PASS);
        $host        = \parse_url($urlString, PHP_URL_HOST);
        $queryString = \parse_url($urlString, PHP_URL_QUERY);
        $fragmentId  = \parse_url($urlString, PHP_URL_FRAGMENT);
        $port        = \parse_url($urlString, PHP_URL_PORT);

        $scheme     = new SchemeName(\parse_url($urlString, PHP_URL_SCHEME));
        $user       = $user ? new StringLiteral($user) : new StringLiteral('');
        $pass       = $pass ? new StringLiteral($pass) : new StringLiteral('');
        $domain     = Domain::specifyType($host);
        $path       = new Path(\parse_url($urlString, PHP_URL_PATH));
        $portNumber = $port ? new PortNumber($port) : new NullPortNumber();
        $query      = $queryString ? new QueryString(\sprintf('?%s', $queryString)) : new NullQueryString();
        $fragment   = $fragmentId ? new FragmentIdentifier(\sprintf('#%s', $fragmentId)) : new NullFragmentIdentifier();

        return new static($scheme, $user, $pass, $domain, $portNumber, $path, $query, $fragment);
    }

    /**
     * Returns a new Url object
     *
     * @param SchemeName          $scheme
     * @param StringLiteral       $user
     * @param StringLiteral       $password
     * @param Domain              $domain
     * @param Path                $path
     * @param PortNumberInterface $port
     * @param QueryString         $query
     * @param FragmentIdentifier  $fragment
     */
    public function __construct(SchemeName $scheme, StringLiteral $user, StringLiteral $password, Domain $domain, PortNumberInterface $port, Path $path, QueryString $query, FragmentIdentifier $fragment)
    {
        $this->scheme             = $scheme;
        $this->user               = $user;
        $this->password           = $password;
        $this->domain             = $domain;
        $this->path               = $path;
        $this->port               = $port;
        $this->queryString        = $query;
        $this->fragmentIdentifier = $fragment;
    }

    /**
     * Tells whether two Url are sameValueAs by comparing their components
     *
     * @param  ValueObjectInterface $url
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $url)
    {
        if (false === Util::classEquals($this, $url)) {
            return false;
        }

        return $this->getScheme()->sameValueAs($url->getScheme()) &&
               $this->getUser()->sameValueAs($url->getUser()) &&
               $this->getPassword()->sameValueAs($url->getPassword()) &&
               $this->getDomain()->sameValueAs($url->getDomain()) &&
               $this->getPath()->sameValueAs($url->getPath()) &&
               $this->getPort()->sameValueAs($url->getPort()) &&
               $this->getQueryString()->sameValueAs($url->getQueryString()) &&
               $this->getFragmentIdentifier()->sameValueAs($url->getFragmentIdentifier())
        ;
    }

    /**
     * Returns the domain of the Url
     *
     * @return Hostname|IPAddress
     */
    public function getDomain()
    {
        return clone $this->domain;
    }

    /**
     * Returns the fragment identifier of the Url
     *
     * @return FragmentIdentifier
     */
    public function getFragmentIdentifier()
    {
        return clone $this->fragmentIdentifier;
    }

    /**
     * Returns the password part of the Url
     *
     * @return StringLiteral
     */
    public function getPassword()
    {
        return clone $this->password;
    }

    /**
     * Returns the path of the Url
     *
     * @return Path
     */
    public function getPath()
    {
        return clone $this->path;
    }

    /**
     * Returns the port of the Url
     *
     * @return PortNumberInterface
     */
    public function getPort()
    {
        return clone $this->port;
    }

    /**
     * Returns the query string of the Url
     *
     * @return QueryString
     */
    public function getQueryString()
    {
        return clone $this->queryString;
    }

    /**
     * Returns the scheme of the Url
     *
     * @return SchemeName
     */
    public function getScheme()
    {
        return clone $this->scheme;
    }

    /**
     * Returns the user part of the Url
     *
     * @return StringLiteral
     */
    public function getUser()
    {
        return clone $this->user;
    }

    /**
     * Returns a string representation of the url
     *
     * @return string
     */
    public function __toString()
    {
        $userPass = '';
        if (false === $this->getUser()->isEmpty()) {
            $userPass = \sprintf('%s@', $this->getUser());

            if (false === $this->getPassword()->isEmpty()) {
                $userPass = \sprintf('%s:%s@', $this->getUser(), $this->getPassword());
            }
        }

        $port = '';
        if (false === NullPortNumber::create()->sameValueAs($this->getPort())) {
            $port = \sprintf(':%d', $this->getPort()->toNative());
        }

        $urlString = \sprintf('%s://%s%s%s%s%s%s', $this->getScheme(), $userPass, $this->getDomain(), $port, $this->getPath(), $this->getQueryString(), $this->getFragmentIdentifier());

        return $urlString;
    }
}

class Address implements ValueObjectInterface
{
    /**
     * Name of the addressee (natural person or company)
     * @var StringLiteral
     */
    protected $name;

    /** @var Street */
    protected $street;

    /**
     * District/City area
     * @var StringLiteral
     */
    protected $district;

    /**
     * City/Town/Village
     * @var StringLiteral
     */
    protected $city;

    /**
     * Region/County/State
     * @var StringLiteral
     */
    protected $region;

    /**
     * Postal code/P.O. Box/ZIP code
     * @var StringLiteral
     */
    protected $postalCode;

    /** @var Country */
    protected $country;

    /**
     * Returns a new Address from native PHP arguments
     *
     * @param string $name
     * @param string $street_name
     * @param string $street_number
     * @param string $district
     * @param string $city
     * @param string $region
     * @param string $postal_code
     * @param string $country_code
     * @return self
     * @throws \BadMethodCallException
     */
    public static function fromNative()
    {
        $args = \func_get_args();

        if (\count($args) != 8) {
            throw new \BadMethodCallException('You must provide exactly 8 arguments: 1) addressee name, 2) street name, 3) street number, 4) district, 5) city, 6) region, 7) postal code, 8) country code.');
        }

        $name       = new StringLiteral($args[0]);
        $street     = new Street(new StringLiteral($args[1]), new StringLiteral($args[2]));
        $district   = new StringLiteral($args[3]);
        $city       = new StringLiteral($args[4]);
        $region     = new StringLiteral($args[5]);
        $postalCode = new StringLiteral($args[6]);
        $country    = Country::fromNative($args[7]);

        return new static($name, $street, $district, $city, $region, $postalCode, $country);
    }

    /**
     * Returns a new Address object
     *
     * @param StringLiteral $name
     * @param Street        $street
     * @param StringLiteral $district
     * @param StringLiteral $city
     * @param StringLiteral $region
     * @param StringLiteral $postalCode
     * @param Country $country
     */
    public function __construct(StringLiteral $name, Street $street, StringLiteral $district, StringLiteral $city, StringLiteral $region, StringLiteral $postalCode, Country $country)
    {
        $this->name       = $name;
        $this->street     = $street;
        $this->district   = $district;
        $this->city       = $city;
        $this->region     = $region;
        $this->postalCode = $postalCode;
        $this->country    = $country;
    }

    /**
     * Tells whether two Address are equal
     *
     * @param  ValueObjectInterface $address
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $address)
    {
        if (false === Util::classEquals($this, $address)) {
            return false;
        }

        return $this->getName()->sameValueAs($address->getName())             &&
               $this->getStreet()->sameValueAs($address->getStreet())         &&
               $this->getDistrict()->sameValueAs($address->getDistrict())     &&
               $this->getCity()->sameValueAs($address->getCity())             &&
               $this->getRegion()->sameValueAs($address->getRegion())         &&
               $this->getPostalCode()->sameValueAs($address->getPostalCode()) &&
               $this->getCountry()->sameValueAs($address->getCountry())
        ;
    }

    /**
     * Returns addressee name
     *
     * @return StringLiteral
     */
    public function getName()
    {
        return clone $this->name;
    }

    /**
     * Returns street
     *
     * @return Street
     */
    public function getStreet()
    {
        return clone $this->street;
    }

    /**
     * Returns district
     *
     * @return StringLiteral
     */
    public function getDistrict()
    {
        return clone $this->district;
    }

    /**
     * Returns city
     *
     * @return StringLiteral
     */
    public function getCity()
    {
        return clone $this->city;
    }

    /**
     * Returns region
     *
     * @return StringLiteral
     */
    public function getRegion()
    {
        return clone $this->region;
    }

    /**
     * Returns postal code
     *
     * @return StringLiteral
     */
    public function getPostalCode()
    {
        return clone $this->postalCode;
    }

    /**
     * Returns country
     *
     * @return Country
     */
    public function getCountry()
    {
        return clone $this->country;
    }

    /**
     * Returns a string representation of the Address in US standard format.
     *
     * @return string
     */
    public function __toString()
    {
        $format = <<<ADDR
%s
%s
%s %s %s
%s
ADDR;

        $addressString = \sprintf($format, $this->getName(), $this->getStreet(), $this->getCity(), $this->getRegion(), $this->getPostalCode(), $this->getCountry());

        return $addressString;
    }
}

class Vocollection implements ValueObjectInterface
{
    /** @var \SplFixedArray */
    protected $items;

    /**
     * Returns a new Vocollection object
     *
     * @param  \SplFixedArray $array
     * @return self
     */
    public static function fromNative()
    {
        $array = \func_get_arg(0);
        $items = array();

        foreach ($array as $item) {
            if ($item instanceof \Traversable || \is_array($item)) {
                $items[] = static::fromNative($item);
            } else {
                $items[] = new StringLiteral(\strval($item));
            }
        }

        $fixedArray = \SplFixedArray::fromArray($items);

        return new static($fixedArray);
    }

    /**
     * Returns a new Vocollection object
     *
     * @return self
     */
    public function __construct(\SplFixedArray $items)
    {
        foreach ($items as $item) {
            if (false === $item instanceof ValueObjectInterface) {
                $type = \is_object($item) ? \get_class($item) : \gettype($item);
                throw new \InvalidArgumentException(\sprintf('Passed SplFixedArray object must contains "ValueObjectInterface" objects only. "%s" given.', $type));
            }
        }

        $this->items = $items;
    }

    /**
     * Tells whether two Collection are equal by comparing their size and items (item order matters)
     *
     * @param  ValueObjectInterface $collection
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $collection)
    {
        if (false === Util::classEquals($this, $collection) || false === $this->count()->sameValueAs($collection->count())) {
            return false;
        }

        $arrayCollection = $collection->toArray();

        foreach ($this->items as $index => $item) {
            if (!isset($arrayCollection[$index]) || false === $item->sameValueAs($arrayCollection[$index])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the number of objects in the collection
     *
     * @return Natural
     */
    public function count()
    {
        return new Natural($this->items->count());
    }

    /**
     * Tells whether the Collection contains an object
     *
     * @param  ValueObjectInterface $object
     * @return bool
     */
    public function contains(ValueObjectInterface $object)
    {
        foreach ($this->items as $item) {
            if ($item->sameValueAs($object)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a native array representation of the Collection
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items->toArray();
    }

    /**
     * Returns a native string representation of the Collection object
     *
     * @return string
     */
    public function __toString()
    {
        $string = \sprintf('%s(%d)', \get_class($this), $this->count()->toNative());

        return $string;
    }
}

class KeyValuePair implements ValueObjectInterface
{
    /** @var ValueObjectInterface */
    protected $key;

    /** @var ValueObjectInterface */
    protected $value;

    /**
     * Returns a KeyValuePair from native PHP arguments evaluated as strings
     *
     * @param string $key
     * @param string $value
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function fromNative()
    {
        $args = func_get_args();

        if (count($args) != 2) {
            throw new \BadMethodCallException('This methods expects two arguments. One for the key and one for the value.');
        }

        $keyString   = \strval($args[0]);
        $valueString = \strval($args[1]);
        $key   = new StringLiteral($keyString);
        $value = new StringLiteral($valueString);

        return new static($key, $value);
    }

    /**
     * Returns a KeyValuePair
     *
     * @param ValueObjectInterface $key
     * @param ValueObjectInterface $value
     */
    public function __construct(ValueObjectInterface $key, ValueObjectInterface $value)
    {
        $this->key   = $key;
        $this->value = $value;
    }

    /**
     * Tells whether two KeyValuePair are equal
     *
     * @param  ValueObjectInterface $keyValuePair
     * @return bool
     */
    public function sameValueAs(ValueObjectInterface $keyValuePair)
    {
        if (false === Util::classEquals($this, $keyValuePair)) {
            return false;
        }

        return $this->getKey()->sameValueAs($keyValuePair->getKey()) && $this->getValue()->sameValueAs($keyValuePair->getValue());
    }

    /**
     * Returns key
     *
     * @return ValueObjectInterface
     */
    public function getKey()
    {
        return clone $this->key;
    }

    /**
     * Returns value
     *
     * @return ValueObjectInterface
     */
    public function getValue()
    {
        return clone $this->value;
    }

    /**
     * Returns a string representation of the KeyValuePair in format "$key => $value"
     *
     * @return string
     */
    public function __toString()
    {
        $string = sprintf('%s => %s', $this->getKey(), $this->getValue());

        return $string;
    }
}

class Dictionary extends Vocollection
{
    /**
     * Returns a new Dictionary object
     *
     * @param  array $array
     * @return self
     */
    public static function fromNative()
    {
        $array = \func_get_arg(0);
        $keyValuePairs = array();

        foreach ($array as $arrayKey => $arrayValue) {
            $key = new StringLiteral(\strval($arrayKey));

            if ($arrayValue instanceof \Traversable || \is_array($arrayValue)) {
                $value = Vocollection::fromNative($arrayValue);
            } else {
                $value = new StringLiteral(\strval($arrayValue));
            }

            $keyValuePairs[] = new KeyValuePair($key, $value);
        }

        $fixedArray = \SplFixedArray::fromArray($keyValuePairs);

        return new static($fixedArray);
    }

    /**
     * Returns a new Dictionary object
     *
     * @param \SplFixedArray $key_value_pairs
     */
    public function __construct(\SplFixedArray $key_value_pairs)
    {
        foreach ($key_value_pairs as $keyValuePair) {
            if (false === $keyValuePair instanceof KeyValuePair) {
                $type = \is_object($keyValuePair) ? \get_class($keyValuePair) : \gettype($keyValuePair);
                throw new \InvalidArgumentException(\sprintf('Passed SplFixedArray object must contains "KeyValuePair" objects only. "%s" given.', $type));
            }
        }

        $this->items = $key_value_pairs;
    }

    /**
     * Returns a Collection of the keys
     *
     * @return Collection
     */
    public function keys()
    {
        $count     = $this->count()->toNative();
        $keysArray = new \SplFixedArray($count);

        foreach ($this->items as $key => $item) {
            $keysArray->offsetSet($key, $item->getKey());
        }

        return new Vocollection($keysArray);
    }

    /**
     * Returns a Vocollection of the values
     *
     * @return Vocollection
     */
    public function values()
    {
        $count       = $this->count()->toNative();
        $valuesArray = new \SplFixedArray($count);

        foreach ($this->items as $key => $item) {
            $valuesArray->offsetSet($key, $item->getValue());
        }

        return new Vocollection($valuesArray);
    }

    /**
     * Tells whether $object is one of the keys
     *
     * @param  ValueObjectInterface $object
     * @return bool
     */
    public function containsKey(ValueObjectInterface $object)
    {
        $keys = $this->keys();

        return $keys->contains($object);
    }

    /**
     * Tells whether $object is one of the values
     *
     * @param  ValueObjectInterface $object
     * @return bool
     */
    public function containsValue(ValueObjectInterface $object)
    {
        $values = $this->values();

        return $values->contains($object);
    }
}

class Path extends StringLiteral
{
    public function __construct($value)
    {
        $filteredValue = parse_url($value, PHP_URL_PATH);

        if (null === $filteredValue || strlen($filteredValue) != strlen($value)) {
            throw new InvalidNativeArgumentException($value, array('string (valid url path)'));
        }

        $this->value = $filteredValue;
    }
}

