<?php
// import required files.
require_once 'common.php';
require_once 'RoundClearing.php';

// checks if the admin ends a round or starts a round.
if (isset($_GET['round_action'])) {
    $action = $_GET['round_action'];
    $settingsDAO = new SettingsDAO();

    // if the admin ends round, set bidding to not allowed and do the appropriate processing for the round.
    // returns a message indicating if round ended or not.
    if ($action == 'End Round') {
        if ($settingsDAO->getRoundNumber() == 1 && $settingsDAO->getBiddingAllowed() == 1) {
            $settingsDAO->setBiddingAllowed(0);
            RoundOneClearing();
            $_SESSION['round-message'] = "Round ended.";

        }
        elseif ($settingsDAO->getRoundNumber() == 2 && $settingsDAO->getBiddingAllowed() == 1) {
            $settingsDAO->setBiddingAllowed(0);
            RoundTwoEnd();
        }
        else {
            $_SESSION['round-message'] = "Either invalid round or round has already ended.";

        }

    }
    // if the admin starts a round, check if it is possible to do so (i.e. round 1 has ended and admin wants to start round 2).
    // returns the message indicating if round start is successful or the error message.
    elseif ($action == 'Start Round') {
        if ($settingsDAO->getRoundNumber() == 1 && $settingsDAO->getBiddingAllowed() == 0) {
            $settingsDAO->setRoundNumber(2);
            $settingsDAO->setBiddingAllowed(1);
            $_SESSION['round-message'] = 'Round 2 has started.';
        }
        else {
            $_SESSION['round-message'] = "Cannot start round if: (1) it is not round 1 and (2) round 1 has not ended.";
        }
    }

}

// if the admin did not end or start a round, it is an invalid request.
// returns appropriate error message.
else {
    $_SESSION['round-message'] = "Invalid request.";
}

// redirects back to admin homepage.
header ("Location: ../admin_homepage.php");
exit;

?>