<?php
/**
 * Created by IntelliJ IDEA.
 * User: anton
 * Date: 9/29/16
 * Time: 4:22 PM
 */
# Information expert / Domain layer
class Row
{
    private $price;
    private $currency;

    public function __construct($price, $currency)
    {
        $this->price = $price;
        $this->currency = $currency;
    }

    public function normalize(Normalizer $normalizer)
    {
        $normalizer->normalize($this);
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}

class CsvReader
{
    public static function getRows()
    {
        return [
            new Row(100, 'myr'),
            new Row(200, 'myr'),
            new Row(300, 'myr'),
            new Row(400, 'myr'),
        ];
    }
}
# Low coupling
class CurrencyConverter
{
    private $currencyConversions = [
        'myr' => 1.4
    ];

    public function convert($price, $currencyCode)
    {
        return $this->currencyConversions[$currencyCode] * $price;
    }
}
# Low coupling
class Normalizer
{
    private $currencyConverter;
 # Protected variation - CurrencyConverter logic/behaviour can be different/extended
    public function __construct(CurrencyConverter $currencyConverter)
    {
        $this->currencyConverter = $currencyConverter;
    }

    public function normalize(Row $row)
    {
        $row->setPrice($this->currencyConverter->convert($row->getPrice(), $row->getCurrency()));

        return $row;
    }
}

class MyIterator
{
    private $normalizer;

    public function __construct(Normalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function iterate(array $rows)
    {
        foreach ($rows as $index => $row) {
            echo 'row ' . $index . ' ' . $row->getPrice() . PHP_EOL;
            $row->normalize($this->normalizer);
            echo 'my normalized price is ' . $row->getPrice() . PHP_EOL;
        }

        return $rows;
    }
}
# Pure Fabrication (to satisfy high cohesion) / Creator
class NormalizerFactory
{
    public static function create()
    {
        return new Normalizer(new CurrencyConverter);
    }
}
# Pure Fabrication / Creator
class IteratorFactory
{
    public static function create()
    {
        $normalizer = NormalizerFactory::create();

        return new MyIterator($normalizer);
    }
}
# Entry point
class Controller
{
    public function main()
    {
        $rows = CsvReader::getRows();
        $iterator = IteratorFactory::create();
        $iterator->iterate($rows);
    }
}

$controller = new Controller;

$controller->main();