<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Data\Traits;

use Saloon\XmlWrangler\XmlWriter;
use Symfony\Component\Yaml\Yaml;

/**
 * Adds YAML and XML serialization capabilities to Spatie Laravel Data objects.
 *
 * This trait extends data objects with additional markup format serialization methods,
 * complementing the built-in JSON and array transformations. Both methods leverage
 * the object's array representation to ensure consistent data structure across all
 * serialization formats.
 *
 * ```php
 * final class UserData extends Data
 * {
 *     use MarkupSerializationTrait;
 *
 *     public function __construct(
 *         public string $name,
 *         public string $email,
 *     ) {}
 * }
 *
 * $user = new UserData('John Doe', 'john@example.com');
 * $yaml = $user->toYaml();
 * $xml = $user->toXml('user');
 * ```
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait MarkupSerializationTrait
{
    /**
     * Serializes the data object to YAML format.
     *
     * Converts the object to its array representation and serializes it using
     * Symfony's YAML component. The resulting YAML preserves the data structure
     * and can be used for configuration files or human-readable data export.
     *
     * @return string YAML-formatted string representation of the data object
     */
    public function toYaml(): string
    {
        // @phpstan-ignore-next-line Spatie Data/Dto exposes toArray
        return Yaml::dump($this->toArray());
    }

    /**
     * Serializes the data object to XML format with a custom root element.
     *
     * Converts the object to its array representation and serializes it using
     * Saloon's XmlWriter. The root element name is customizable to match your
     * XML schema requirements.
     *
     * @param  string $root Name of the XML root element wrapping the data structure
     * @return string XML-formatted string representation with specified root element
     */
    public function toXml(string $root = 'root'): string
    {
        // @phpstan-ignore-next-line Spatie Data/Dto exposes toArray
        return XmlWriter::make()->write($root, $this->toArray());
    }
}
