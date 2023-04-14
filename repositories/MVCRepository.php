<?php

namespace repositories;

use PDO;
use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\database\QueryBuilder;
use lib\core\exceptions\SystemException;
use lib\helper\StringHelper;
use models\entities\Session;
use models\entities\Token;
use models\SessionModel;

/**
 * @inheritDoc
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class MVCRepository extends ARepository {
    private QueryBuilder $query_builder;

    /**
     * @throws SystemException
     */
    public function __construct() {
        $cm = App::getInstanceOf(ConnectionManager::class);
        $this->pdo = $cm->getConnection("mvc");
        $this->query_builder = App::getInstanceOf(QueryBuilder::class, null, ["pdo" => $this->pdo]);
    }

    /**
     * @param string $id
     * @return SessionModel
     * @throws SystemException
     */
    public function getSession( string $id ): SessionModel {
        try {
            $this->query_builder->Select()
                ->From("sessions")
                ->Where("id=:id")
            ;
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(':id', $id);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, SessionModel::class);
            return $this->pdo->execute()->fetch();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param string $id
     * @return array
     * @throws SystemException
     */
    public function getSessionAsArray( string $id ): array {
        try {
            $this->query_builder->Select()
                ->From("sessions")
                ->Where("id=:id")
            ;
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(':id', $id);
            return $this->pdo->execute()->fetch();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     *
     * @param Session $session
     * @return void
     * @throws SystemException
     */
    public function createSession( SessionModel $session ): void {
        try {
            $this->query_builder->Insert("sessions")
                ->Columns(["id", "actor_id", "ip", "expired"]);
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(':id', $session->id);
            $this->pdo->bindParam(':actor_id', $session->actor_id, PDO::PARAM_INT);
            $this->pdo->bindParam(':ip', $session->ip);
            $this->pdo->bindParam(':expired', $session->expired);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param Session $session
     * @return void
     * @throws SystemException
     */
    public function updateSession( SessionModel $session ): void {
        if( $session->id === "" ) {
            return;
        }

        try {
            $curr_id = $session->id;
            if( $session->_rotate_session ) {
                $session->id = StringHelper::getGuID();
            }
            $this->query_builder->Update("sessions")
                ->Set(["id", "actor_id", "as_actor", "ip", "expired"])
                ->Where("id=:curr_id")
            ;
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(':id', $session->id);
            $this->pdo->bindParam(':curr_id', $curr_id);
            $this->pdo->bindParam(':actor_id', $session->actor_id, PDO::PARAM_INT);
            $this->pdo->bindParam(':as_actor', $session->as_actor, PDO::PARAM_INT);
            $this->pdo->bindParam(':ip', $session->ip);
            $this->pdo->bindParam(':expired', $session->expired);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param Session $session
     * @return void
     * @throws SystemException
     */
    public function deleteSession( SessionModel $session ): void {
        if( $session->id === "" ) {
            return;
        }

        try {
            $this->query_builder->Delete("sessions")
                ->Where("id=:id");
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(":id", $session->id, PDO::PARAM_INT);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param string $id
     * @return Token
     * @throws SystemException
     */
    public function getToken( string $id ): Token {
        try {
            $this->query_builder->Select()
                ->From("tokens")
                ->Where("id=:id")
            ;
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(":id", $id);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, Token::class);
            return $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param string $id
     * @return array
     * @throws SystemException
     */
    public function getTokenAsArray( string $id ): array {
        try {
            $this->query_builder->Select()
                ->From("tokens")
                ->Where("id=:id")
            ;
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(":id", $id);
            return $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param Token $token
     * @return void
     * @throws SystemException
     */
    public function createToken( Token $token): void {
        try {
            $this->query_builder->Insert("tokens")
                ->Columns(["id", "expired"])
            ;
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(":id", $token->id);
            $this->pdo->bindParam(":expired", $token->expired);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param Token $token
     * @return void
     * @throws SystemException
     */
    public function updateToken( Token $token): void {
        if( $token->id === "" ) {
            return;
        }

        try {
            $this->query_builder->Update("tokens")
                ->Set(["expired"])
                ->Where("id=:id")
            ;
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(":id", $token->id);
            $this->pdo->bindParam(":expired", $token->expired);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param Token $token
     * @return void
     * @throws SystemException
     */
    public function deleteToken( Token $token ): void {
        if( $token->id === "" ) {
            return;
        }

        try {
            $this->query_builder->Delete("tokens")
                ->Where("id=:id")
            ;
            $this->pdo->useQuerybuilder($this->query_builder);
            $this->pdo->bindParam(":id", $token->id);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}