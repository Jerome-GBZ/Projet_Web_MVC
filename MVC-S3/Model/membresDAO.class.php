<?php
  class MembresDAO {
    private $db;

    function __construct(){
      $database = 'sqlite:'.dirname(__FILE__).'/data/vinyles.db';
      try {
        $this->db = new PDO($database);
      } catch (\PDOException $e) {
        die("erreur de connexion : ".$e->getMessage());
      }
    }

    function getAllMembres() {
      $reqAllMembres = "SELECT * FROM membres";
      $req_getAllMembres = $this->db->prepare($reqAllMembres);
      $req_getAllMembres->execute();
      $res_getAllMembres = $req_getAllMembres->fetchAll();
      return $res_getAllMembres;
    }

    function getMembres(string $email):Membres {
      $reqMembres = "SELECT * FROM membres WHERE email = :email";
      $req_getMembres = $this->db->prepare($reqMembres);
      $req_getMembres->BindParam(':email', $email);
      $req_getMembres->execute();
      $res_getMembres = $req_getMembres->fetchAll(PDO::FETCH_CLASS, 'Membres');
      return $res_getMembres[0];
    }

    // cette fonction va avoir pour but de verifier si l'email existe deja en gros si lutilitateur a deja un compte
    function verfieEmailExist(string $email):int {
      $reqmail = $this->db->prepare("SELECT * FROM membres WHERE email = ?"); // remplacer "" par '' ?
      $reqmail->execute(array($email));
      // $mailexist2 = $reqmail->rowCount(); ne fonctionne pas ! ducoup on utilise le count()
      $row = $reqmail->fetchAll();
      $mailexist = count($row);

      // si = 1 le mail y est deja
      if ($mailexist == 1) {
        $res = 1;
      } else {  // si = 0 le mail n'est pas encore enregistrer
        $res = 0;
      }
      return $res;
    }

    function inscrireUnMembre(STRING $name, STRING $prenom, STRING $email, STRING $mdp_hash):void {
      $id = uniqid();
      $email_verif = 0;
      $newsletters = 1;

      $requette = $this->db->prepare('INSERT INTO membres VALUES(:id, :name, :prenom, :email, :newsletters, :password, :email_verif)');
      $requette->execute(array(
          'id' => $id,
          'name' => $name,
          'prenom' => $prenom,
          'email' => $email,
          'newsletters' => $newsletters,
          'password' => $mdp_hash,
          'email_verif' => $email_verif));
    }

    function envoieUnMailConfirmation(Membres $m):void {
      $email = $m->getemail();

      $header="MIME-Version: 1.0\r\n";
      $header.='From:"Projet php"<projet.php.mail@gmail.com>'."\n";
      $header.='Content-Type:text/html; charset="uft-8"'."\n";
      $header.='Content-Transfer-Encoding: 8bit';

      $message='
      <html>
        <body>
          <div align="center">
            <h1> Le Viny </h1>
            <p> Bonjour '.$m->getprenom().',</p>
            <p> <br>Avant de pouvoir interagir avec les différentes fonctionnalités de notre site, vous devez confirmer votre compte en cliquant sur le lien si dessous. </p>
            <a href="http://localhost/MVC-S3/Controler/confirmation_mail.ctrl.php?name='.$m->getname(). '&email=' .$email. '&id='.$m->getid().'"> Confirmez votre compte !</a>
            <p> <br><br>Si vous n\'etes pas à l\'origine de cette action merci de nous informer à l\'adresse suivante : contact@leviny.fr </p>
            <p> <br><br>A bientôt, <br>L\'équipe Le Viny.</p>
          </div>
        </body>
      </html>
      ';

      mail($email, "Confirmation de compte - Projet PHP", $message, $header);
    }

    function update_mail_confirmation(Membres $m):void {
      $updateMembre = $this->db->prepare("UPDATE membres SET mail_verif = '1' WHERE name = ? AND email = ? AND id = ?");
      $updateMembre->execute(array($m->getname(), $m->getemail(), $m->getid()));
    }

    function delete_un_membre(Membres $m):void {
      $deleteMembre = $this->db->prepare("DELETE FROM membres WHERE id = ? AND email = ?");
      $deleteMembre->execute(array($m->getid(), $m->getemail()));
    }
  }
?>
