var bb1 = { "coordinates": [{ "lon": -27.41025517544554, "lat": 65.93831606806292 }, { "lon": -23.52996451034306, "lat": 62.38126628445526 }, { "lon": -12.622315912984627, "lat": 63.99426057747471 }, { "lon": -14.932908900422499, "lat": 67.84455208343832 }, { "lon": -27.41025517544554, "lat": 65.93831606806292 }] };

var bb2 = { "coordinates": [{ "lon": -24.504600511336537, "lat": 63.78288747076437 }, { "lon": -22.5461610827927, "lat": 61.71995367654687 }, { "lon": -11.057881615103348, "lat": 63.328185851832686 }, { "lon": -12.110413103244722, "lat": 65.54678199314678 }, { "lon": -24.504600511336537, "lat": 63.78288747076437 }] };


//function DoBoundingBoxesIntersect(bb1, bb2) {
function DoBoundingBoxesIntersect(bb1, bb2) {

    //First bounding box, top left corner, bottom right corner
    var ATLx = bb1.coordinates[0].lon;   //bb1.TopLeftLatLong.Longitude;
    var ATLy = bb1.coordinates[0].lat;    //bb1.TopLeftLatLong.Latitude;
    var ABRx = bb1.coordinates[2].lon;   //bb1.BottomRightLatLong.Longitude;
    var ABRy = bb1.coordinates[2].lat;   //bb1.BottomRightLatLong.Latitude;

    alert(ATLx);

    //Second bounding box, top left corner, bottom right corner
    var BTLx = bb2.coordinates[0].lon;   //bb2.TopLeftLatLong.Longitude;
    var BTLy = bb2.coordinates[0].lat;    //bb2.TopLeftLatLong.Latitude;
    var BBRx = bb2.coordinates[2].lon;   //bb2.BottomRightLatLong.Longitude;
    var BBRy = bb2.coordinates[2].lat;   //bb2.BottomRightLatLong.Latitude;
    alert(BTLx);

    var rabx = Math.abs(ATLx + ABRx - BTLx - BBRx);
    var raby = Math.abs(ATLy + ABRy - BTLy - BBRy);
    //rAx + rBx
    var raxPrbx = ABRx - ATLx + BBRx - BTLx;
    //rAy + rBy
    var rayPrby = ATLy - ABRy + BTLy - BBRy;
    if (rabx <= raxPrbx && raby <= rayPrby) {
        //alert(rabx." <= ".raxPrbx." && ".raby." <= ".rayPrby);
        alert("they intersect");
        return TRUE;
    }
    alert("they do not intersect");
    return FALSE;
}
