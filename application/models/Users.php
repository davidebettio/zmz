<?php

class Model_Users extends Zend_Db_Table_Abstract
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $_primary = 'user_id';
    protected $_name = 'users';
    protected $_rowClass = 'Model_Row_User';

    public function add($values)
    {
        $nowSql = Zmz_Date::getSqlDateTime();
        $row = $this->createRow();
        $row->username = $values['username'];
        $row->password = $this->hashPassword($values['password']);
        $row->email = $values['email'];
        $row->locale = Zmz_Culture::getLocale();
        $row->timezone = Zmz_Culture::getTimezone();
        $row->status = self::STATUS_INACTIVE;
        $row->date_registration = $nowSql;
        $row->code = $this->generateCode();
        $row->date_code = $nowSql;
        $row->group_id = Model_Groups::USER;

        $row->save();

        if ($row->user_id) {
            return $row;
        } else {
            throw new Exception('User was not saved on database');
        }
    }

    /**
     *
     * @param string $usernameOrEmail
     * @param string $password
     * @param boolean $isHashPassword
     * @return <type>
     */
    public function findCredentials($usernameOrEmail, $password, $isHashPassword = false)
    {
        $isHashPassword = (bool) $isHashPassword;
        if (!$isHashPassword) {
            $password = self::hashPassword($password);
        }

        $select = $this->select()->setIntegrityCheck(false)
                        ->from(array('u' => 'users'))
                        ->join(array('g' => 'groups'), 'g.group_id = u.group_id', array(
                            'group' => 'g.group'
                        ))
                        ->where('u.email = ? OR u.username = ?', $usernameOrEmail)
                        ->where('u.password = ?', $password);

        $row = $this->fetchRow($select);

        return $row;
    }

    public function findById($id)
    {
        $select = $this->select()
                        ->where('user_id = ?', $id);
        $row = $this->fetchRow($select);

        return $row;
    }

    public function findByUsername($username)
    {
        $select = $this->select()
                        ->where('username = ?', $username);
        $row = $this->fetchRow($select);

        return $row;
    }

    public function findByIdOrUsername($userId = null, $username = null)
    {
        if ($userId || $username) {
            if ($userId) {
                $userRow = $this->findById($userId);
            } elseif ($username) {
                $userId = false;
                $userRow = $this->findByUsername($username);
            }
        } else {
            $userRow = false;
        }

        return $userRow;
    }

    public function findByEmail($email)
    {
        $select = $this->select()
                        ->where('email = ?', $email);
        $row = $this->fetchRow($select);

        return $row;
    }

    public static function hashPassword($password)
    {
        // $password = hash('sha256', $password);
        return $password;
    }

    public static function generateCode()
    {
        $code = sha1(uniqid(null, true));

        return $code;
    }

}

