<?php namespace MinistryPlatformAPI\OAuth;


class Credentials
{
    /**
     * Grant type for this instance of the credentials. Currently supported
     * possibilities are client_credentials and authorization_code grant types.
     *
     * @var null
     */
    private $grantType = null;

    /**
     * Refresh token returned from request
     * @var null
     */
    private $refresh_token = null;

    /**
     * Access token returned from oAuth request
     * @var null
     */
    private $access_token = null;

    /**
     * ID token that is part of authorization_code grant type 
     * @var null
     */
    private $id_token = null;

    /**
     * Token duration in seconds
     * @var null 
     */
    private $expires_in = null;

    /**
     * Should always be Bearer
     * @var null 
     */
    private $token_type = null;

    /**
     * The time an access token expires - calculated from the $expires_in
     * value
     * 
     * @var null 
     */
    private $expiresAtTimestamp = null;
    private $expiresAt = null;

    /**
     * The time that the token was acquired
     * @var null
     */
    private $acquisitionTimestamp = null;
    private $acquisitionTime = null;

    /**
     * For authorization_code grant type we also have access to
     * user information.  We should get and save it here.
     * @var null
     */
    private $userInfo = null;

    public function __construct($grant_type)
    {
        $this->grantType = $grant_type;
    }

    /**
     * General get method to return the value of an attribute
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->$key;
    }

    /**
     * Return the access token if it is valid
     * @return string
     */
    public function getAccessToken()
    {
        if (is_string($this->access_token) && $this->isValidToken() ) {
            $this->accessToken = $this->token_type . ' ' . $this->access_token;

            return $this->accessToken;
        }

        return false;
    }

    /**
     * Return the Refresh Token, if available and valid
     * @return string
     */
    public function getRefreshToken()
    {
        if (is_string($this->refresh_token) && $this->isValidToken() ){

            $this->refreshToken = $this->token_type . ' ' . $this->refresh_token;

            return $this->refreshToken;
        }

        return false;
    }

    /**
     * If grant type also includes user information, return it
     * @return bool|null
     */
    public function getUserInfo()
    {
        return ($this->userInfo) ? $this->userInfo : false;
    }
    /**
     * Get duration of token.  Defaults to minutes.  If otherwise specified,
     * it will return seconds.
     *
     * @param string $units | defaults to minutes.  Otherwise, will assume seconds.
     * @return float|int
     */
    public function getExpiration($units = 'minutes')
    {
        $minutes = $units == 'minutes' ? $this->expires_in / 60 : $this->expires_in;
        // $minutes = 1;
        
        return $minutes;
    }

    /**
     * Set one or an array of attributes for the class
     * @param $key | single attribute name OR an array or attribute=>value pairs*
     * @param null $value for single attributes, the value of the attribute
     */
    public function set($key, $value=null)
    {
        // Set the time that this token was saved for the first time
        $this->setAcquisitionTime();

        if (is_null($value) && is_array($key)) {
            // An array of attributes was passed in so save each one
            foreach($key as $attribute => $value){
                $this->setValue($attribute, $value);
            }
        } else {
            // Not an array so just set the attribute
            $this->setValue($key, $value);
        }
    }

    /**
     * Check to see if token is still valid
     * @return bool
     */
    public function isValidToken()
    {
        return ($this->expiresAtTimestamp > time() ) ? true : false;
    }

    /**
     * Set a single attribute value
     * @param $key
     * @param $value
     */
    private function setValue($key, $value)
    {
        $this->$key = $value;

        if ($key == 'expires_in') $this->setExpiration($value);
    }

    /**
     * Set the expiration time for the token
     * @param $expires_in
     */
    private function setExpiration($expires_in)
    {
        $this->expiresAtTimestamp = time() + $expires_in;
        $this->expiresAt = date('Y-m-d H:i:s', time() + $expires_in);
    }

    /**
     * Set the acquisition time of the token
     */
    private function setAcquisitionTime()
    {
        if (is_null($this->acquisitionTime)) {
            $this->acquisitionTime = date('Y-m-d H:i:s');
            $this->acquisitionTimestamp = time();
        };
    }

}