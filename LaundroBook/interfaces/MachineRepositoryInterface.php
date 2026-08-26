<?php

interface MachineRepositoryInterface{

    public function findAvailableMachine(int $MachineId);  
    public function returnAvailableMachines(); 

}