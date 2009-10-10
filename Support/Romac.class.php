<?php

/*
* Copyright (c) 2009, Romain Ruetschi <romain.ruetschi@gmail.com>
* Code licensed under the BSD License:
* See http://romac.github.com/files/BSD.txt
*/

/**
* Source file containing class Romac.
* 
* @package    Romac
* @license    http://romac.github.com/files/BSD.txt New BSD License
* @author     Romain Ruetschi <romain.ruetschi@gmail.com>
* @version    0.1
* @see        Romac
*/

/**
* Class Romac.
* 
* Description for class Romac.
*
* @package    Romac
* @license    http://romac.github.com/files/BSD.txt New BSD License
* @author     Romain Ruetschi <romain.ruetschi@gmail.com>
* @version    0.1
*/
final class Romac
{
    
    const DS                        = DIRECTORY_SEPARATOR;
    const DEFAULT_CLASS_SEPARATOR   = '_';
    const NAMESPACE_CLASS_SEPARATOR = '\\';
    const DEFAULT_PACKAGE           = 'default';
    
    /**
     * A reference to this class instance.
     *
     * @var Romac object.
     */
    private static $_instance = NULL;
    
    /**
     * Environment variables.
     *
     * @var array
     */
    protected $_variables     = array();
    
    /**
     * Prefix keys
     *
     * @var array
     */
    protected $_prefixKeys = array(
        'RR_PREFIX',
        'ROMAC_PREFIX',
        'ROMAC_CLASS_PREFIX'
    );
    
    /**
     * Suffix
     *
     * @var string
     */
    protected $_classSuffixes = array(
        '.class.php',
        '.php'
    );
    
    /**
     * Directories to skip from filename.
     *
     * @var array
     */
    protected $_skipDirectories = array(
        'Classes',
        'Tests'
    );
    
    /**
     * Class constructor.
     * The constructor of this class cannot be used to instanciate a Romac object.
     * This class is a singleton class, this means that there is only one instance
     * of the Romac class and this instance is shared by every one who does a call
     * to Romac::getInstance().
     *
     * @see getInstance to know how to create the Romac instance or to get a reference to it.
     */
    private function __construct()
    {
        if( array_key_exists( 'TM_FILENAME', $_SERVER ) ) {
            
            $this->_variables =& $_SERVER;
            
        } else if( array_key_exists( 'TM_FILENAME', $_ENV ) ) {
            
            $this->_variables =& $_ENV;
        }
        
        if( array_key_exists( 'RR_CLASS_FOLDERS', $this->_variables ) ) {
            
            $this->_skipDirectories += $this->trimExplode(
                ',',
                $this->_variables[ 'RR_CLASS_FOLDERS' ]
            );
        }
    }
    
    /**
     * Creates or returns the only instance of the Romac class.
     *
     * @return Romac the only instance of the Romac class.
     */
    public static function getInstance()
    {
        if( !is_object( self::$_instance ) ) {
            
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Create a clone of the instance.
     * Because this class is a singleton class, there can exists only one
     * instance of this class.
     * So this method will throw an exception if someone tries to call it.
     *
     * @throws BadMethodCallException An new BadMethodCallException instance
     * @return void
     */
    final public function __clone()
    {
        throw new BadMethodCallException(
            'This class is a singleton class, you are not allowed to clone it.' . "\n" .
            'Please call ' . get_class( $this ) . '::getInstance() to get a reference to ' .
            'the only instance of the ' . get_class( $this ) . ' class.'
        );
    }
    
    /**
     * This method is called when unserialize is called on a serialized representation
     * of an instance of this class.
     * Because this class is a singleton class, there can exists only one
     * instance of this class.
     * So this method will throw an exception if someone tries to call it.
     *
     * @throws BadMethodCallException An new BadMethodCallException instance
     * @return void
     */
    final public function __wakeup()
    {
        throw new BadMethodCallException(
            'This class is a singleton class, you are not allowed to unserialize ' .
            'it as this could create a new instance of it.' . "\n" .
            'Please call ' . get_class( $this ) . '::getInstance() to get a reference to ' .
            'the only instance of the ' . get_class( $this ) . ' class.'
        );
    }
    
    public function getClassName( $classSeparator = self::DEFAULT_CLASS_SEPARATOR, $prefix = true )
    {
        $path = $this->_variables[ 'TM_FILEPATH' ];
        
        if( !empty( $this->_variables[ 'TM_PROJECT_DIRECTORY' ] ) ) {
            
            $className = str_replace(
                $this->_variables[ 'TM_PROJECT_DIRECTORY' ],
                '',
                $path
            );
            
            $className = str_replace( self::DS, $classSeparator, $className );
            $className = substr( $className, 1 );
        
        } else {
            
            $className = explode( self::DS, $path );
            $className = array_pop( $className );
        }
        
        foreach( $this->_classSuffixes as $suffix ) {
            
            $className = str_replace( $suffix, '', $className );
        }
        
        foreach( $this->_skipDirectories as $skipDirectory ) {
            
            $className = str_replace( $skipDirectory . $classSeparator, '', $className );
        }
        
        $prefix = ( $prefix ) ? $this->getPrefix() : '';
        $prefix = ( $prefix && empty( $this->_variables[ 'RR_AVOID_2PREFIX' ] ) )
                ? $prefix . $classSeparator
                : '';
        
        return $prefix . $className;
    }
    
    public function getPackageName()
    {
        if( isset( $this->_variables[ 'RR_PACKAGE' ] ) ) {
            
            return $this->_variables[ 'RR_PACKAGE' ];
        }
        
        return $this->getPrefix();
    }
    
    public function getNamespacedClassName()
    {
        $fullClassName      = $this->getClassName(
            self::NAMESPACE_CLASS_SEPARATOR
        );
        
        $fullClassNameParts = explode(
            self::NAMESPACE_CLASS_SEPARATOR,
            $fullClassName
        );
        
        return array_pop( $fullClassNameParts );
    }
    
    public function getNamespace()
    {
        $fullClassName      = $this->getClassName(
            self::NAMESPACE_CLASS_SEPARATOR
        );
        
        $fullClassNameParts = explode(
            self::NAMESPACE_CLASS_SEPARATOR,
            $fullClassName
        );
        
        array_pop( $fullClassNameParts );
        
        if( !empty( $this->_variables[ 'RR_AVOID_2PREFIX' ] ) ) {
            
            array_shift( $fullClassNameParts );
        }
        
        return implode( self::NAMESPACE_CLASS_SEPARATOR, $fullClassNameParts );
    }
    
    public function getPrefix( $default = '' )
    {
        foreach( $this->_prefixKeys as $key ) {
            
            if( !empty( $this->_variables[ $key ] ) ) {
                
                return $this->_variables[ $key ];
            }
        }
        
        return $default;
    }
    
    /**
     * Explodes a string and trims all values for whitespace in the ends.
     * If $onlyNonEmptyValues is set, then all blank ('') values are removed.
     *
     * @param string  $delimiter         Delimiter string to explode with.
     * @param string  $string            The string to explode.
     * @param boolean $removeEmptyValues If set, all empty values will be removed in output.
     * @param integer $limit             If positive, the result will contain a maximum of
     *                                   $limit elements, if negative, all components except
     *                                   the last -$limit are returned, if zero (default),
     *                                   the result is not limited at all. Attention though
     *                                   that the use of this parameter can slow down this
     *                                   function.
     * @return  array Exploded values.
     * @license GPL v2
     * @package TYPO3 (www.typo3.org)
     */
    public function trimExplode( $delimiter, $string, $removeEmptyValues = true, $limit = 0 )
    {
        $explodedValues = explode( $delimiter, $string );
        $explodedValues = array_map( 'trim', $explodedValues );
        
        if( $removeEmptyValues ) {
            
            $temp = array();
            
            foreach( $explodedValues as $value ) {
                
                if( $value !== '' ) {
                    
                    $temp[] = $value;
                }
            }
            
            $explodedValues = $temp;
        }
        
        if( $limit !== 0 ) {
            
            $explodedValues = array_slice( $explodedValues, 0, $limit );
        }
        
        return $explodedValues;
    }
    
}
