<?php

/**
 * @property Kategoria_model Kategoria_model
 * @property Ogloszenia_model Ogloszenia_model
 * @property Parametry_ogloszenia_model Parametry_ogloszenia_model
 * @property Usery_model Usery_model
 * @property Wiadomosci_model Wiadomosci_model
 * @property Zdjecia_model Zdjecia_model
 */
class Ogloszenia extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
        $this->load->helper('html');
        // ja to tam sie nie pier***, tylko laduje wszystkie modele 💩 XD
        $this->load->model('Kategoria_model');
        $this->load->model('Ogloszenia_model');
        $this->load->model('Parametry_ogloszenia_model');
        $this->load->model('Usery_model');
        $this->load->model('Wiadomosci_model');
        $this->load->model('Zdjecia_model');
        $this->load->model('Category_model');
        if($this->session->userdata('username') == ''){

            redirect('Logginc/ero');
        }
    }


    /**
     * Wyswietla wszystkie ogloszenia, które są aktywne
     */
    public function index()
    {
        $katy = $this->Category_model->cat();
        $arr['katy'] = $katy;
        $ogloszenia = $this->Ogloszenia_model->getAllAnnos();
        $query['ogloszenia']= $ogloszenia;

        $this->load->view('templates/header', $arr);
        $this->load->view('ogloszenia', $query);
        $this->load->view('templates/footer');
    }

    /**
     * Wyświetla jedno ogloszenie (o podanym id)
     */
    public function jedno($id_ogloszenia)
    {

        $katy = $this->Category_model->cat();
        $arr['katy'] = $katy;
        $ogloszenie = $ogloszenia = $this->Ogloszenia_model->getAnnoById($id_ogloszenia);
        if (!$ogloszenie) {
            return "Brak";
        }

        $parametry_ogloszenia = $this->Parametry_ogloszenia_model->getParameters($id_ogloszenia);
        $zdjecia_byid = $this->Zdjecia_model->getByIdOgloszenia($id_ogloszenia);
        $query['ogloszenie']=$ogloszenie;
        $query['parametry_ogloszenia']=$parametry_ogloszenia;
        $query['zdjecia_byid']=$zdjecia_byid;
        $kontakt = $this->Ogloszenia_model->getviewAnnoById($id_ogloszenia);
        $query['kontakt']=$kontakt;
        $this->load->view('templates/header', $arr);
        $this->load->view('jedno', $query);
        $this->load->view('templates/footer');
    }

    /**
     * Dodawanie ogloszenia
     */
    public function dodaj()
    {
        $katy = $this->Category_model->cat();
        $arr['katy'] = $katy;
        $ogloszenia = $this->Ogloszenia_model->getAllAnnos();
        $kat = $this->Kategoria_model->getAllCategories();
        $kata = $this->input->post('Kategoria');

        $config['upload_path'] = './zdjecia/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $this->load->library('upload', $config);

        $query['ogloszenia']= $ogloszenia;
        $query['kat']= $kat;
        $query['kata']=$kata;
        $this->load->library('form_validation');

        $this->form_validation->set_rules('Tytul', 'Tytul', 'required',
        array('required'=>'Tytul jest wymagany'));
        $this->form_validation->set_rules('Opis', 'Opis', 'required',
        array('required'=>'Opis jest wymagany'));
        $this->form_validation->set_rules('Kategoria', 'Kategoria', 'required',
        array('required'=>'Kategoria jest wymagana'));
        $this->form_validation->set_rules('Cena', 'Cena', 'required|numeric',
        array('required'=>'Cena jest wymagana', 'numeric'=>'tylko liczby'));

      //  $this->form_validation->set_rules('zdjecie', 'zdjecie', 'required');

        $ty = $this->input->post('Tytul');
        $op = $this->input->post('Opis');
        $ka = $this->input->post('Kategoria');
        $ce = $this->input->post('Cena');
        $us = $this->session->userdata('Id_usera');

        if ($this->form_validation->run() == FALSE)
        {
            $this->load->view('templates/header', $arr);
            $this->load->view('ogloszenieadd', $query);
            $this->load->view('templates/footer');

        }
        else
        {
            $config['upload_path'] = './zdjecia/';
            $config['allowed_types'] = 'gif|jpg|png';
            $this->load->library('upload', $config);
            $this->upload->do_upload('zdjecie');

            $upload_data =  $this->upload->data();
            $file_name = $upload_data['file_name'];
            $array = array('Tytul'=>$ty, 'Opis'=>$op, 'Cena'=>$ce, 'Id_kategorii'=>$ka, 'Id_usera'=>$us, 'Main_zdj'=> './zdjecia/'.$file_name);
           if($this->Ogloszenia_model->addNewAnno($array))
           {

               $this->mojeOgloszenia();
           }
           else
           {
               echo "drobne niepowodzenie";
           }
        }
    }

    public function edytuj($id)
    {
        $katy = $this->Category_model->cat();
        $arr['katy'] = $katy;
        $ogloszenia = $this->Ogloszenia_model->getAnnoById($id);
        $query['ogloszenia'] = $ogloszenia;
        $this->load->library('form_validation');

        $this->form_validation->set_rules('Tytul', 'Tytul', 'required',
        array('required'=>'Tytul jest wymagany'));
        $this->form_validation->set_rules('Opis', 'Opis', 'required',
        array('required'=>'Opis jest wymagany'));
        $this->form_validation->set_rules('Cena', 'Cena', 'required',
        array('required'=>'Cena jest wymagany'));


        if ($this->form_validation->run() == FALSE) {
            $this->load->view('templates/header', $arr);
            $this->load->view('ogloszenieEdit', $query);
            $this->load->view('templates/footer');

        } else {
            $ty = $this->input->post('Tytul');
            $op = $this->input->post('Opis');
            $ce = $this->input->post('Cena');


            $array = array('Tytul' => $ty, 'Opis' => $op, 'Cena' => $ce);
            if ($this->Ogloszenia_model->editAnno($id, $array)==TRUE) {

                $this->mojeOgloszenia();

            } else {

                echo "drobne niepowodzenie";
            }
        }
    }

    /**
     * Wyswietla ogloszenia danego uzytkownika
     */
    public function mojeOgloszenia()
    {
        $katy = $this->Category_model->cat();
        $arr['katy'] = $katy;

        $id_usera = $this->session->userdata('Id_usera');
        $moje = $this->Ogloszenia_model->getAnnoByIdUsera($id_usera);
        if (!$moje) {
            $this->load->view('brak_ogloszen');
            $this->dodaj();
        }
        $wyg = $this->Ogloszenia_model->getExpiredAnnosByIdUsera($id_usera);
        $query['moje']=$moje;
        $query['wyg']=$wyg;

        $this->load->view('templates/header', $arr);
        $this->load->view('ogloszeniamoje', $query);
        $this->load->view('templates/footer');
    }

    public function usun($id)
    {

       if($this->Ogloszenia_model->deleteAnno($id)==TRUE)
       {
           $this->load->view('delete_anno');
           redirect('Ogloszenia/mojeOgloszenia');
       }
       else
       {
           echo 'WSPANIALE HEHEH';
       }
    }

    /**
     * Przedluza ogloszenie ($id) o miesiac
     */
    public function przedloz($id)
    {
        $katy = $this->Category_model->cat();
        $arr['katy'] = $katy;

        if($this->Ogloszenia_model->setNewExpiredDate($id)==TRUE)
        {
            redirect('Ogloszenia/mojeOgloszenia');
        }
        else
        {
            echo 'WSPANIALE HEHEH';
        }

    }

    public function wyroznij($id)
    {
        if($this->Ogloszenia_model->HighlightAnno($id)==TRUE)
        {
                $this->load->view('wyroznienie_success');
                redirect('Ogloszenia/mojeOgloszenia');
        }
        else
        {
            echo 'Coś poszło nie tak';
        }
    }

    public function odwyroznij($id)
    {
        if($this->Ogloszenia_model->deHighlightAnno($id)==TRUE)
        {
                $this->load->view('odwyroznienie_success');
                redirect('Ogloszenia/mojeOgloszenia');
        }
        else
        {
            echo 'WSPANIALE HEHEH';
        }
    }

    public function kategorie($id_kategorii)
    {
        $katy = $this->Category_model->cat();
        $arr['katy'] = $katy;
        $ogloszenia = $this->Ogloszenia_model->getAnnoByIdKategorii($id_kategorii);
        if (!$ogloszenia) {
            echo "Brak";
        }

        $query['ogloszenia']=$ogloszenia;

        $this->load->view('templates/header', $arr);
        $this->load->view('pokategorii', $query);
        $this->load->view('templates/footer');

    }

    public function kup($id)
    {
        if($this->Ogloszenia_model->kupuj($id)==TRUE)
        {
            $this->load->view('kup');
            redirect('Ogloszenia/mojeOgloszenia');
        }
        else
        {
            echo 'WSPANIALE HEHEH';
        }
    }
}
