/* Your custom js here */

function changeImage()
{
    var image =  document.getElementById("imgClickAndChange");
    if (image.getAttribute('src') == "/static/img/committee.jpg")
    {
        image.src = "/static/img/bier.gif";
    }
    else
    {
        image.src = "/static/img/committee.jpg";
    }
}
