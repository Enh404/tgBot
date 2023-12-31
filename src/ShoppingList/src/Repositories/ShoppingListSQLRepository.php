<?php

namespace ShoppingList\Repositories;

use General\Adapters\PDOAdapter;

class ShoppingListSQLRepository extends PDOAdapter
{
    public function checkProductListExist($username)
    {
        $sql = "SELECT spisok
            FROM spisok_pokypok
            WHERE username = '$username'
            LIMIT 1";
        $spisok = $this->selectOneRow($sql);
        if ($spisok) {
            return true;
        }
        return false;
    }

    public function createProductList($username, $spisok)
    {
        $sql = "INSERT INTO spisok_pokypok(
                username,
                spisok
            )
                VALUES ('$username', '$spisok')";
        $this->insert($sql);
    }

    public function getProductsByUsername($username)
    {
        $sql = "SELECT spisok
            FROM spisok_pokypok
            WHERE username = '$username'
            LIMIT 1";
        return explode(',', $this->selectOneRow($sql)['spisok']);
    }

    public function updateProductList($username, $spisok)
    {
        $sql = "UPDATE spisok_pokypok
            SET spisok = '$spisok'
            WHERE username = '$username'";
        $this->update($sql);
    }

    public function deleteRow($username)
    {
        $sql = "DELETE FROM spisok_pokypok
            WHERE username = '$username'";
        $this->delete($sql);
    }
}
