
PuntosYupick = Class.create();
PuntosYupick.prototype = {
    
    initialize: function()
    {
        this.yupickResultados = '';
        
        Event.observe(window, 'load', function() 
        {
            
            $$('.main').invoke('observe', 'click', function(e)
            {
                var element = e.element();
                //OJO: prueba error
                if (element.getValue)
                {
                    if (element.getValue() == 'yupick_yupick')
                    {
                        PuntosYupick.getPuntosYupick();
                    }
                }
            });
        });
        
    },

    getPuntosYupick:function()
    {
        if ($('direccion').value == '' && $('codigoPostal').value == '') {
        	alert('Debe introducir la Calle y/o el Código Postal');
        	$('direccion').focus();
        } else {
	        this.reloadurl = $('checkUrl').value;
	        Element.show('loadingmask');
	        if (!$('option_parking').checked) {
	        	$('option_parking').value = '';
	        } else {
	        	$('option_parking').value = 'on';
	        }
	        if (!$('option_wifi').checked) {
	        	$('option_wifi').value = '';
	        } else {
	        	$('option_wifi').value = 'on';
	        }
	        if (!$('option_alimentacion').checked) {
	        	$('option_alimentacion').value = '';
	        } else {
	        	$('option_alimentacion').value = 'on';
	        }
	        if (!$('option_prensa').checked) {
	        	$('option_prensa').value = '';
	        } else {
	        	$('option_prensa').value = 'on';
	        }
	        if (!$('option_tarjeta').checked) {
	        	$('option_tarjeta').value = '';
	        } else {
	        	$('option_tarjeta').value = 'on';
	        }
	        if (!$('option_mas20').checked) {
	        	$('option_mas20').value = '';
	        } else {
	        	$('option_mas20').value = 'on';
	        }
	        if (!$('option_sabados').checked) {
	        	$('option_sabados').value = '';
	        } else {
	        	$('option_sabados').value = 'on';
	        }
	        if (!$('option_domingos').checked) {
	        	$('option_domingos').value = '';
	        } else {
	        	$('option_domingos').value = 'on';
	        }
	        
	        new Ajax.Request(this.reloadurl, {
	            method: 'post',
	            //OJO Cambios
	            parameters: {direccion: $('direccion').value, 
	            	codigoPostal: $('codigoPostal').value, 
	            	option_parking: $('option_parking').value, 
	            	option_wifi: $('option_wifi').value, 
	            	option_alimentacion: $('option_alimentacion').value, 
	            	option_prensa: $('option_prensa').value, 
	            	option_tarjeta: $('option_tarjeta').value, 
	            	option_mas20: $('option_mas20').value, 
	            	option_sabados: $('option_sabados').value, 
	            	option_domingos: $('option_domingos').value},
	            onComplete: this.reloadChildren.bind(this)
	        });
        }
        
    },
    
    reloadChildren: function(transport){
        Element.hide('loadingmask');
        $('oficinas_yupick_content').setStyle({display: 'block'});
        var jsonResponse=transport.responseText.evalJSON(true);
        this.yupickResultados = jsonResponse;

        this.fillDropDownYupick ($('oficinas_yupick'), this.yupickResultados.puntoentrega);
    },
    
    fillDropDownYupick:function (field, data)
    {
        
        for(i=field.options.length-1;i>=0;i--) { field.remove(i); }       

        data.each(  
            function(e) {  
                field.options.add(new Option(e.nombre+' - '+e.direccion+" - "+e.localidad+' ('+e.provincia+')',e.id));  
            }  
        );      
        
        this.yupickInfo() 
 
    },
    
    changeYupickOptions:function ()
    {
        $('yupick_carrier_principal').toggle();
        $('yupick_carrier_secundario').toggle();
        if ($('yupick_carrier_secundario').visible())
        {
            $('yupick_options_text').replace('<span id="yupick_options_text">Menos opciones de b&uacute;squeda</span>');
        } else {
            $('yupick_options_text').replace('<span id="yupick_options_text">M&aacute;s opciones de b&uacute;squeda</span>');
        }
    },
    
    yupickInfo:function()
    {
        
        var puntoActual = $('oficinas_yupick').value;
        this.yupickResultados.puntoentrega.each(  
            function(e) { 
                if (e.id == puntoActual)
                {
                    $('yupick_info_map').setStyle({display: 'block'});
                    $('yupick_info_time').setStyle({display: 'block'});
                    $('yupick_info_user').setStyle({display: 'block'});
                    
                    $('oficinas_yupick_data').value = e.nombre+' - '+e.direccion+" - "+e.localidad+' ('+e.provincia+')';
                    
                    // info del punto
                    this.infoGoogleMaps(e);
                    this.infoHorarios(e);
                    
                }
            }.bind(this));
        
    },
    
    infoGoogleMaps:function(e)
    {        
        var latlng = new google.maps.LatLng(e.poslatitud, e.poslongitud);
        var myOptions = {
          zoom: 16,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        
        var imagen = new google.maps.MarkerImage($('skinPath').value + 'frontend/default/default/images/yupick/globo.png', new google.maps.Size(100,47), new google.maps.Point(0,0), new google.maps.Point(50,47));
        var sombra = new google.maps.MarkerImage($('skinPath').value + 'frontend/default/default/images/yupick/globosombra.png', new google.maps.Size(100,19), new google.maps.Point(0,0), new google.maps.Point(31,19));
        
        var map = new google.maps.Map(document.getElementById("yupick_info_map"), myOptions);
               
        var beachMarker = new google.maps.Marker({
            position: latlng,
            map: map,
            icon: imagen,
            shadow: sombra
        });

    },
    
    
    infoHorarios: function (e)
    {
        
        var tablaHorarios = '<table><tr><th>&nbsp;</th><th>Ma&ntilde;ana</th><th>Tarde</td></tr>';
        tablaHorarios += '<tr class="odd"><td>Lunes</td><td>'+e.horario.lunes.manana+'</td><td>'+e.horario.lunes.tarde+'</td></tr>';
        tablaHorarios += '<tr class="even"><td>Martes</td><td>'+e.horario.martes.manana+'</td><td>'+e.horario.martes.tarde+'</td></tr>';
        tablaHorarios += '<tr class="odd"><td>Mi&eacute;rcoles</td><td>'+e.horario.miercoles.manana+'</td><td>'+e.horario.miercoles.tarde+'</td></tr>';
        tablaHorarios += '<tr class="even"><td>Jueves</td><td>'+e.horario.jueves.manana+'</td><td>'+e.horario.jueves.tarde+'</td></tr>';
        tablaHorarios += '<tr class="odd"><td>Viernes</td><td>'+e.horario.viernes.manana+'</td><td>'+e.horario.viernes.tarde+'</td></tr>';
        tablaHorarios += '<tr class="even"><td>S&aacute;bado</td><td>'+e.horario.sabado.manana+'</td><td>'+e.horario.sabado.tarde+'</td></tr>';
        tablaHorarios += '<tr class="odd"><td>Domingo</td><td>'+e.horario.domingo.manana+'</td><td>'+e.horario.domingo.tarde+'</td></tr>';
        tablaHorarios += '</table>';

        $('yupick_info_time').update(tablaHorarios);
        
    }
}
    
var PuntosYupick = new PuntosYupick();

var win = null;

function nuevaVentana(mypage, myname, w, h, scroll)
{
	lpos = (screen.width) ? (screen.width-w)/2 : 0;
	tpos = (screen.height) ? (screen.height-h)/2 : 0;
	settings = 'height=' + h + ',width=' + w + ',top=' + tpos + ',left=' + lpos + ',scrollbars=' + scroll + ',resizable';
	//alert(settings);
	win = window.open(mypage,myname,settings);
}
