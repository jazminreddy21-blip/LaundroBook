<?php

    interface ServiceRepositoryInterface{
        //read, update and delete stored services

        public function getServices();
        public function updateService();
        public function deleteService();  


    }