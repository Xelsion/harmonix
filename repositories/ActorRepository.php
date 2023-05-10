<?php

namespace repositories;

use PDO;
use DateTime;
use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\helper\StringHelper;
use models\entities\Actor;
use models\entities\ActorRole;
use models\AccessPermissionModel;
use models\ActorModel;
use models\ActorRoleModel;
use models\ActorTypeModel;

/**
 * @inheritDoc
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class ActorRepository extends ARepository {

    /**
     * @throws SystemException
     */
    public function __construct() {
        $cm = App::getInstanceOf(ConnectionManager::class);
        $this->pdo = $cm->getConnection("mvc");
    }

    /**
     * @param int $id
     * @return Actor
     * @throws SystemException
     */
    public function get( int $id ) : ActorModel {
        try {
            $actor = $this->pdo->Select()
                ->From("actors")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(":id", $id, PDO::PARAM_INT)
                ->fetchMode(PDO::FETCH_INTO, App::getInstanceOf(ActorModel::class))
                ->execute()
                ->fetch()
            ;
            if( !$actor ) {
                $actor = new ActorModel();
            }
            return $actor;
        } catch ( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param int $id
     * @return array
     * @throws SystemException
     */
    public function getAsArray( int $id ) : array {
        try {
            $actor = $this->pdo->Select()
                ->From("actors")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(":id", $id, PDO::PARAM_INT)
                ->execute()
                ->fetch()
            ;
            if( !$actor ) {
                $actor = array();
            }
            return $actor;
        } catch ( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param string $email
     * @return ActorModel
     * @throws SystemException
     */
    public function getByLogin( string $email ) : ActorModel {
        try {
            $actor = $this->pdo->Select()
                ->From("actors")
                ->Where("email=:email")
                    ->And("login_disabled=0")
                    ->And("deleted")->isNull()
                ->prepareStatement()
                    ->withParam(":email", $email)
                ->fetchMode(PDO::FETCH_CLASS, ActorModel::class)
                ->execute()
                ->fetch()
            ;
            if( !$actor ) {
                $actor = new ActorModel();
            }
            return $actor;
        } catch ( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Returns am array of Actors
     *
     * @return array of Actor's
     * @throws SystemException
     */
    public function getAll(): array {
        try {
            return $this->pdo->Select()
                ->From("actors")
                ->prepareStatement()
                ->fetchMode(PDO::FETCH_CLASS, ActorModel::class)
                ->execute()
                ->fetchAll()
            ;
        } catch ( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param array $conditions
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return array
     * @throws SystemException
     */
    public function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1): array {
        return $this->findIn("actors", ActorModel::class, $conditions, $order, $direction, $limit, $page);
    }

    /**
     * @param Actor $actor
     * @return mixed
     * @throws SystemException
     */
    public function getActorType( ActorModel $actor ): ActorTypeModel {
        return App::getInstanceOf(ActorTypeModel::class, null, ["id" => $actor->type_id]);
    }

    /**
     * Returns the actor role for the given controller method
     * If non is setClass for a specific method it will look for a
     * controller role and if non is setClass too, it will look for
     * the domain role
     *
     * @param Actor $actor
     * @param string $controller
     * @param string $method
     * @param mixed|string $domain
     *
     * @return ActorRole
     *
     * @throws SystemException
     */
    public function getActorRole( ActorModel $actor, string $controller, string $method, string $domain = SUB_DOMAIN ): ActorRoleModel {
        try {
            // do we have a loaded actor object?
            if( $actor->id > 0 ) {
                // check role for the given domain, controller and method
                $result = $this->pdo->Select("role_id")
                    ->From("access_permissions")
                    ->Where("actor_id=:actor_id")
                        ->And("domain=:domain")
                        ->And("controller=:controller")
                        ->And("method=:method=:method")
                    ->prepareStatement()
                        ->withParam(":actor_id", $actor->id, PDO::PARAM_INT)
                        ->withParam(":domain", $domain)
                        ->withParam(":controller", $controller)
                        ->withParam(":method", $method)
                    ->execute()
                    ->fetch()
                ;
                if( $result ) {
                    return App::getInstanceOf(ActorRoleModel::class, NULL, ["id" => (int)$result["role_id"]]);
                }

                // check role for the given domain and controller
                $result = $this->pdo->Select("role_id")
                    ->From("access_permissions")
                    ->Where("actor_id=:actor_id")
                        ->And("domain=:domain")
                        ->And("controller=:controller")
                    ->prepareStatement()
                        ->withParam(":actor_id", $actor->id, PDO::PARAM_INT)
                        ->withParam(":domain", $domain)
                        ->withParam(":controller", $controller)
                    ->execute()
                    ->fetch()
                ;
                if( $result ) {
                    return App::getInstanceOf(ActorRoleModel::class, NULL, ["id" => (int)$result["role_id"]]);
                }

                // check role for the given domain
                $result = $this->pdo->Select("role_id")
                    ->From("access_permissions")
                    ->Where("actor_id=:actor_id")
                        ->And("domain=:domain")
                    ->prepareStatement()
                        ->withParam(":actor_id", $actor->id, PDO::PARAM_INT)
                        ->withParam(":domain", $domain)
                    ->execute()
                    ->fetch()
                ;
                if( $result ) {
                    return App::getInstanceOf(ActorRoleModel::class, NULL, ["id" => (int)$result["role_id"]]);
                }
            } else {
                $result = $this->pdo->Select("id")
                    ->From("actor_roles")
                    ->Where("is_default=1")
                    ->prepareStatement()
                    ->execute()
                    ->fetch()
                ;
                if( $result ) {
                    return App::getInstanceOf(ActorRoleModel::class, NULL, ["id" => (int)$result["id"]]);
                }
            }

            return App::getInstanceOf(ActorRoleModel::class);
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }


    /**
     * Returns an array of AccessPermission's for the given Actor
     *
     * @param Actor $actor
     * @return array of AccessPermission's
     * @throws SystemException
     */
    public function getActorPermissions( ActorModel $actor ): array {
        try {
            return $this->pdo->Select()
                ->From("access_permissions")
                ->Where("actor_id=:actor_id")
                ->prepareStatement()
                    ->withParam(":actor_id", $actor->id, PDO::PARAM_INT)
                ->fetchMode(PDO::FETCH_CLASS, AccessPermissionModel::class)
                ->execute()
                ->fetchAll()
            ;
        } catch ( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Returns the total number of actors
     *
     * @return int
     * @throws SystemException
     */
    public function getNumRows(): int {
        $result = $this->pdo->Select("COUNT(DISTINCT id)")->As("num_count")
            ->From("actors")
            ->prepareStatement()
            ->execute()
            ->fetch()
        ;
        return (int)$result["num_count"];
    }

    /**
     * @param Actor $actor
     * @return void
     * @throws SystemException
     */
    public function createObject( Actor $actor ): void {
        try {
            $actor->password = StringHelper::getBCrypt($actor->password);

            $this->pdo->Insert("actors")
                ->Columns(["type_id", "email", "password", "first_name", "last_name", "login_fails", "login_disabled"])
                ->prepareStatement()
                    ->withParam(':type_id', $actor->type_id)
                    ->withParam(':email', $actor->email)
                    ->withParam(':password', $actor->password)
                    ->withParam(':first_name', $actor->first_name)
                    ->withParam(':last_name', $actor->last_name)
                    ->withParam(':login_fails', $actor->login_fails, PDO::PARAM_INT)
                    ->withParam(':login_disabled', $actor->login_disabled, PDO::PARAM_INT)
                ->execute()
            ;
            $actor->id = $this->pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param Actor $actor
     * @return void
     * @throws SystemException
     */
    public function updateObject( Actor $actor ): void {
        try {
            $row = $this->pdo->Select("password")
                ->From("actors")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(':id', $actor->id, PDO::PARAM_INT)
                ->execute()
                ->fetch()
            ;
            if( !empty($row) ) {
                if( $actor->password !== '' && $row["password"] !== $actor->password ) {
                    $actor->password = StringHelper::getBCrypt($actor->password);
                } else {
                    $actor->password = $row["password"];
                }
                $this->pdo->Update("actors")
                    ->Set(["email", "password", "first_name", "last_name", "login_fails", "login_disabled", "deleted"])
                    ->Where("id=:id")
                    ->prepareStatement()
                        ->withParam(':id', $actor->id, PDO::PARAM_INT)
                        ->withParam(':email', $actor->email)
                        ->withParam(':password', $actor->password)
                        ->withParam(':first_name', $actor->first_name)
                        ->withParam(':last_name', $actor->last_name)
                        ->withParam(':login_fails', $actor->login_fails, PDO::PARAM_INT)
                        ->withParam(':login_disabled', $actor->login_disabled, PDO::PARAM_INT)
                        ->withParam(':deleted', $actor->deleted)
                    ->execute()
                ;
            }
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

    }

    /**
     * @param Actor $actor
     * @return void
     * @throws SystemException
     */
    public function deleteObject( Actor $actor ): void {
        if( $actor->id > 0 ) {
            try {
                $this->pdo->Update("actors")
                    ->Set(["deleted", "login_disabled"])
                    ->Where("id=:id")
                    ->prepareStatement()
                        ->withParam(':id', $actor->id, PDO::PARAM_INT)
                        ->withParam(':login_disabled', 1, PDO::PARAM_INT)
                    ->withParam(':deleted', (new DateTime())->format("Y-m-d H:i:s"))
                    ->execute()
                ;
                $actor = new ActorModel();
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

}