<?php

class DemoController extends Controller
{
    public function actionIndex()
    {
        $message = "Welcome";
        $this->render('index',array('message'=>$message));
    }
    public function actionWelcome($name)
    {
        // echo 'dfdf';
            $this->render('welcome',array('name'=>$name));
    }

}