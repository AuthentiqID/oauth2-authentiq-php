<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 14/3/2017
 * Time: 8:28 μμ
 */

namespace Authentiq\OAuth2\Client\Provider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class AuthentiqResourceOwner implements ResourceOwnerInterface
{

    /**
     * Response payload
     *
     * @var array
     */

    protected $data;

    /**
     * AuthentiqResourceOwner constructor.
     * @param array $response
     */
    public function __construct($data = [])
    {
        $this->data =$data;
    }

    /**
     * Retrieves id of resource owner.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->claim('sid');
    }

    /**
     * Retrieves first name of resource owner.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->claim('given_name');
    }

    /**
     * Retrieves last name of resource owner.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->claim('family_name');
    }

    /**
     * Returns a field from the parsed JWT data.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function claim($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * Returns all the data obtained about the user.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}