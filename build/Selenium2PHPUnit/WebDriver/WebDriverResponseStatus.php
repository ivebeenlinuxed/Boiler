<?php
class WebDriverResponseStatus {
    const SUCCESS 	= 0;    //The command executed successfully.
    const NO_SUCH_ELEMENT 	=7;     //An element could not be located on the page using the given search parameters.
    const NO_SUCH_FRAME 	=8;     //A request to switch to a frame could not be satisfied because the frame could not be found.
    const UNKNOWN_COMMAND 	=9;     //The requested resource could not be found, or a request was received using an HTTP method that is not supported by the mapped resource.
    const STALE_ELEMENT_REFERENCE=10;   	//An element command failed because the referenced element is no longer attached to the DOM.
    const ELEMENT_NOT_VISIBLE=11; 	//An element command could not be completed because the element is not visible on the page.
    const INVALID_ELEMENT_STATE=12; 	//An element command could not be completed because the element is in an invalid state (e.g. attempting to click a disabled element).
    const UNKNOWN_ERROR=13; 	//An unknown server-side error occurred while processing the command.
    const ELEMENT_IS_NOT_SELECTABLE=15; 	//An attempt was made to select an element that cannot be selected.
    const JAVASCRIPT_ERROR=17; 	//An error occurred while executing user supplied JavaScript.
    const XPATH_LOOKUP_ERROR=19; 	//An error occurred while searching for an element by XPath.
    const NO_SUCH_WINDOW=23; 	//A request to switch to a different window could not be satisfied because the window could not be found.
    const INVALID_COOKIE_DOMAIN=24; 	//An illegal attempt was made to set a cookie under a different domain than the current page.
    const UNABLE_TO_SET_COOKIE=25; 	//A request to set a cookie's value could not be satisfied.
    const TIMEOUT=28;         //A command did not complete before its timeout expired.
    
}
?>
