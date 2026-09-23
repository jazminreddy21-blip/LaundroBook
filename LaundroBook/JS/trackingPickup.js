/* =========================================================
   LAUNDROBOOK - PICKUP TRACKING
========================================================= */


/*
    The backend will eventually provide this value.

    For now, this is dummy frontend data.

    Change this value to test different statuses:

    order_confirmed
    laundry_received
    washing_in_progress
    drying
    folding
    ready_for_collection
    collected
*/

let currentLaundryStatus = "drying";


/* =========================================================
   LAUNDRY PROGRESS
========================================================= */

function updateLaundryProgress(status){

    const progressSteps =
        document.querySelectorAll(
            "#laundryProgress li"
        );


    /*
        Get the position of the current status.

        Example:

        drying = position 3

        Therefore:
        Order Confirmed       = completed
        Laundry Received      = completed
        Washing               = completed
        Drying                = current
        Folding               = upcoming
        Ready                  = upcoming
        Collected             = upcoming
    */

    const statusOrder = [
        "order_confirmed",
        "laundry_received",
        "washing_in_progress",
        "drying",
        "folding",
        "ready_for_collection",
        "collected"
    ];


    const currentStatusIndex =
        statusOrder.indexOf(status);


    progressSteps.forEach(function(step, index){

        /*
            Remove previous styling.
        */

        step.classList.remove(
            "completed",
            "current"
        );


        /*
            Completed stages.
        */

        if(index < currentStatusIndex){

            step.classList.add("completed");

        }


        /*
            Current stage.
        */

        else if(index === currentStatusIndex){

            step.classList.add("current");

        }

    });

}


/* =========================================================
   INITIALISE PROGRESS
========================================================= */

updateLaundryProgress(currentLaundryStatus);