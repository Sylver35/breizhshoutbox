/**
* @package Breizh Shoutbox extension
* @copyright(c) 2018-2020 Sylver35   https://breizhcode.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

(function($) {  // Avoid conflicts with other libraries
	'use strict';

	lateral.openShout = function(){
		if(!lateral.displayPanel){
			$('#boxforshout').attr('data', lateral.lateralUrl);
		}
		lateral.changeDisplay('dtbox1',false);
		lateral.changeDisplay('dtbox2',true);
		lateral.changeDisplay('dtbox3',true);
		lateral.changeDisplay('boxforshout',true);
	};

	lateral.closeShout = function(){
		if(!lateral.displayPanel){
			$('#boxforshout').attr('data', '');
		}
		lateral.changeDisplay('dtbox1',true);
		lateral.changeDisplay('dtbox2',false);
		lateral.changeDisplay('dtbox3',false);
		lateral.changeDisplay('boxforshout',false);
	};
	
	lateral.changeDisplay = function(id,display){
		if(display){
			$('#'+id).removeClass('no_display').addClass('displayblock');
		}else{
			$('#'+id).removeClass('displayblock').addClass('no_display');
		}
	}
})(jQuery);
