      function pw_showhide(whichLayer,i,hide)
      {
        if (document.getElementById)
        {
          // this is the way the standards work
          var style2 = document.getElementById(whichLayer).style;
          style2.overflow = "hidden";

          if(i == 0)
            if (hide) style2.display = style2.display? "":"block";
            else style2.height = style2.height? "" :"30px";
          else if (i == 1)
            if (hide) style2.display = style2.display? "":"none";
            else style2.height = style2.height? "" :"30px";

        }
        else if (document.all)
        {
          // this is the way old msie versions work
          var style2 = document.all[whichLayer].style;
          style2.display = style2.display? "":"block";
        }
        else if (document.layers)
        {
          // this is the way nn4 works
          var style2 = document.layers[whichLayer].style;
          style2.display = style2.display? "":"block";
        }
      }

function pw_toggle_menue(t) {

            var menu = document.getElementById('framecontentLeft');
            var cont = document.getElementById('maincontent');

            if (menu.style.width == '5px') {
              menu.style.width = '250px';
              cont.style.left = '250px';
              t.style.backgroundImage = 'url(lib/icons/prev.gif)';
            } else {
              menu.style.width = '5px';
              cont.style.left = '5px';
              t.style.backgroundImage = 'url(lib/icons/next.gif)';
            }
            return false;
          };
