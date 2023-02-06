<?php
namespace models\entities;

use Exception;
use lib\App;
use lib\core\blueprints\AEntity;
use lib\core\ConnectionManager;
use lib\core\enums\ActorType;
use lib\core\exceptions\SystemException;
use lib\helper\StringHelper;
use PDO;

/**
 * The ActorModel entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Actor extends AEntity {

	// the columns
	public int $id = 0;

    public int $type_id = ActorType::User->value;

	public string $email = "";

    public string $password = "";

    public string $first_name = "";

    public string $last_name = "";

	public int $login_fails = 0;

	public bool $login_disabled = false;

    public string $created = "";

    public ?string $updated = null;

    public ?string $deleted = null;

    /**
     * The constructor loads the database content into this object.
     * If id is 0 the entity will be empty
     *
     * @param int $id
     *
     * @throws \lib\core\exceptions\SystemException
     */
	public function __construct( int $id = 0 ) {
		if( $id > 0 ) {
            try {
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("SELECT * FROM actors WHERE id=:id");
                $pdo->bindParam(":id", $id, PDO::PARAM_INT);
                $pdo->setFetchMode(PDO::FETCH_INTO, $this);
                $pdo->execute()->fetch();
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage());
            }
        }
	}

    /**
     * @inheritDoc
     */
	public function create(): void {
        try {
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $sql = "INSERT INTO actors (type_id, email, password, first_name, last_name, login_fails, login_disabled) VALUES (:type_id, :email, :password, :first_name, :last_name, :login_fails, :login_disabled)";
            $this->password = StringHelper::getBCrypt($this->password);
            $pdo->prepareQuery($sql);
            $pdo->bindParam(':type_id', $this->type_id);
            $pdo->bindParam(':email', $this->email);
            $pdo->bindParam(':password', $this->password);
            $pdo->bindParam(':first_name', $this->first_name);
            $pdo->bindParam(':last_name', $this->last_name);
            $pdo->bindParam(':login_fails', $this->login_fails, PDO::PARAM_INT);
            $pdo->bindParam(':login_disabled', $this->login_disabled, PDO::PARAM_INT);
            $pdo->execute();
            $this->id = $pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
	}

    /**
     * @inheritDoc
     */
	public function update(): void {
        $cm = App::getInstanceOf(ConnectionManager::class);
        $pdo = $cm->getConnection("mvc");
        $pdo->prepareQuery("SELECT password FROM actors WHERE id=:id");
        $pdo->bindParam(":id", $this->id, PDO::PARAM_INT);
        try {
            if( $row = $pdo->execute()->fetch() ) {
                if( $this->password !== '' && $row["password"] !== $this->password ) {
                    $this->password = StringHelper::getBCrypt($this->password);
                } else {
                    $this->password = $row["password"];
                }
                $sql = "UPDATE actors SET email=:email, password=:password, first_name=:first_name, last_name=:last_name, login_fails=:login_fails, login_disabled=:login_disabled, deleted=:deleted WHERE id=:id";
                $pdo->prepareQuery($sql);
                $pdo->bindParam(':id', $this->id, PDO::PARAM_INT);
                $pdo->bindParam(':email', $this->email);
                $pdo->bindParam(':password', $this->password);
                $pdo->bindParam(':first_name', $this->first_name);
                $pdo->bindParam(':last_name', $this->last_name);
                $pdo->bindParam(':login_fails', $this->login_fails, PDO::PARAM_INT);
                $pdo->bindParam(':login_disabled', $this->login_disabled, PDO::PARAM_INT);
                $pdo->bindParam(':deleted', $this->deleted);
                $pdo->execute();
            }
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @inheritDoc
     */
	public function delete(): bool {
		if( $this->id > 0 ) {
            try {
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("DELETE FROM actors WHERE id=:id");
                $pdo->bindParam("id", $this->id, PDO::PARAM_INT);
                $pdo->execute();
                return true;
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
		}
		return false;
	}

}
