/* Your custom js here */

function changeImage()
{
    var image = document.getElementById("imgClickAndChange");
    if (image.getAttribute('src') == "/static/img/committee.jpg")
    {
    	var set = false;
    	var ca = document.cookie.split(";");
    	for (var i = 0; i < ca.length; i++) 
    	{
    		var c = ca[i];
    		while (c.charAt(0) == ' ') 
    		{
                c = c.substring(1);
            }
            var elem = c.split("=")
            console.log (elem);
            if (elem[0] == "highQuality")
            {
            	if (elem[1] == "true")
            	{
            		image.src = "/static/img/bier-hq.gif";
            		document.getElementById("lowQualityButton").style.visibility = "visible";
            	}
            	else
            	{
            		image.src = "/static/img/bier.gif";
	            	document.getElementById("highQualityButton").style.visibility = "visible";
	            }
            	set = true;
            }
    	}
    	if (!set)
    	{
	        image.src = "/static/img/bier.gif";
	        document.getElementById("highQualityButton").style.visibility = "visible";
    	}
    }
    else
    {
        image.src = "/static/img/committee.jpg";
        document.getElementById("highQualityButton").style.visibility = "hidden";
        document.getElementById("lowQualityButton").style.visibility = "hidden";
    }
}

function highQuality()
{
	var image = document.getElementById("imgClickAndChange");
	image.src = "/static/img/bier-hq.gif";

	var d = new Date();
    d.setTime(d.getTime() + (30*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();

    var name = "highQuality=" + true + ";";

    document.cookie = name + expires;
    console.log (name + expires);

    document.getElementById("highQualityButton").style.visibility = "hidden";
    document.getElementById("lowQualityButton").style.visibility = "visible";
}

function lowQuality()
{
	var image = document.getElementById("imgClickAndChange");
	image.src = "/static/img/bier.gif";

	var d = new Date();
    d.setTime(d.getTime() + (30*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();

    var name = "highQuality=" + false + ";";

    document.cookie = name + expires;
    console.log (name + expires);

    document.getElementById("highQualityButton").style.visibility = "visible";
    document.getElementById("lowQualityButton").style.visibility = "hidden";
}
