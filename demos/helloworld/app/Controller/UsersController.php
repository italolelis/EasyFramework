<?php

class UsersController extends AppController {

    function index() {
        //geting all the users from the database
        $users = $this->Users->all(array("fields" => "id, username, admin"));
        //Passing the $users var to the view
        $this->users = $users;
    }

    function add() {
        //if the $_POST array is empty
        if (!empty($this->data)) {
            //crypt the password, which came from the form
            $this->data["password"] = Security::hash($this->data["password"], "md5");
            //include the user in the DB
            $this->Users->save($this->data);
            //redirect to the index
            //is the same thing do this $this->redirect("/users/index"); or this...
            $this->redirect("/users");
        }
    }

    function edit($id = null) {
        //if the $_POST array is empty...
        if (empty($this->data)) {
            //we'll render the edit view
            //we get the first user that matches the id passed in the URL
            $user = $this->Users->first(array("fields" => "id, username", "conditions" => "id=$id"));
            //Passing the $usuario var to the view
            $this->user = $user;
        } else {
            //the $_POST isn't empty so we get the id passed in the URL and put in the data array
            $this->data["id"] = $id;
            //Now we're going to update the user, that is possible because we passed the id in the data array
            //so the framework will not be confused to if you want to include or update the register
            $this->Users->save($this->data);
            //redirect to the index
            //is the same thing do this $this->redirect("users/index"); or this...
            $this->redirect("/users");
        }
    }

    function delete($id = null) {
        //don't render any view to this action
        $this->setAutoRender(false);
        //delete the user which have the id passed through GET (URL)
        $this->Users->delete($id);
        //redirect to the index
        //is the same thing do this $this->redirect("usuarios/index"); or this...
        $this->redirect("/users");
    }

}

?>
