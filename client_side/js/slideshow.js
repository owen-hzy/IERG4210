$(document).ready(function(){                
		var FocusNum=new Object();
		FocusNum.Id="slider";	/*Locate the position to perform slide effect*/
		FocusNum.Time=7000;		/*Set the waiting time. Units:ms*/
		$("#"+FocusNum.Id).css("position","relative");		/*set the slider's css position property to be relative*/
		$("#"+FocusNum.Id+" ul").attr("id","slides");		/*assign the id slides to the ul element*/
		var Li="#"+FocusNum.Id+" li";
		$(Li+":not(:first)").hide();	/*hide the images other than the first one*/
		var i=0;
		var len=$(Li).length;
		var LstUL ="<ul class='num'>";
		$(Li).each(function(NumI){          /*constrct the navigation tools-1,2,3,4,5 by finding the number of element li*/
			var Numi=NumI+1;
			LstUL +="<li>"+Numi+"</li>";
		});
		LstUL +="</ul>";
		$("#"+FocusNum.Id).append(LstUL);    /*append the navigation tools to the area*/
		$("#"+FocusNum.Id+" .num li:eq(0)").addClass("active");    /*in order to set the active image's css property*/
		function Objstr(){           /*perform the fadeout/fadein effect*/
			var mo=(i+1)%len;
			$(Li+":eq("+i%len+")").fadeOut("slow",function(){
				$(Li+":eq("+mo+")").fadeIn("slow"); 
				$("#"+FocusNum.Id+" .num li").removeClass("active");
				$("#"+FocusNum.Id+" .num li:eq("+mo+")").addClass("active");
			});
			i++;
		};
		var onload= setInterval(function(){Objstr()},FocusNum.Time);      /*use setInterval to perform the function every FocusNum.Time*/
		$("#"+FocusNum.Id+"").hover(function(){      /*stop the slide move when mouse enters the area*/
			clearInterval(onload);
		},function(){
			onload=setInterval(function(){Objstr()},FocusNum.Time);
		});
		$("#"+FocusNum.Id+" .num li").click(function(){         /*change the images when click the navigation tools*/
			$("#slides li").hide();
			$(Li+":eq("+$(this).index()+")").fadeOut("slow",function(){
				$(Li+":eq("+$(this).index()+")").fadeIn("slow"); 
				$("#"+FocusNum.Id+" .num li").removeClass("active");
				$("#"+FocusNum.Id+" .num li:eq("+($(this).index())+")").addClass("active");
			});
			i=$(this).index();
		});
	});  



