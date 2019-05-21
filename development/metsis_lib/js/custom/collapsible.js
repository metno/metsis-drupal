/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *  Rows are given css classes for this unobtrusive JS to work
 *  sample table
 */
//
// <table id="mytable">
// <tbody>
// <tr class="parent">
// <td>Cash and Equivalents</td>
// <td>000,000</td>
// <td>000,000</td>
// </tr>
// <tr class="child">
// <td>Cash and Equivalents</td>
// <td>000,000</td>
// <td>000,000</td>
// </tr>
// </tbody>
// </table>
/**
 *  end sample table
 */
/*
 jQuery(document).ready(function () {
 console.log("ready!");
 });
 */

/**
 * prefer anonymous functions since these are more easily ported to other applications
 */
(function ($) {
    //
    $(document).ready(function () {
        //
        function getChildren($row) {
            var children = [];
            while ($row.next().hasClass('dataset-row odd')) {
                children.push($row.next());
                $row = $row.next();
            }
            return children;
        }

        $('.even').on('click', function () {

            var children = getChildren($(this));
            $.each(children, function () {
                $(this).toggle();
            });
        });
        //
    })
    //
})(jQuery);


/*
 jQuery(document).ready(function () {
 
 function getChildren($row) {
 var children = [];
 while ($row.next().hasClass('dataset-row odd')) {
 children.push($row.next());
 $row = $row.next();
 }
 return children;
 }
 
 jQuery('.even').on('click', function () {
 
 var children = getChildren(jQuery(this));
 jQuery.each(children, function () {
 jQuery(this).toggle();
 });
 });
 });
 */

/**
 * single level
 */
/*
 <html>
 
 <head>
 <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
 <script type="text/javascript">
 $(document).ready(function() {
 
 function getChildren($row) {
 var children = [];
 while($row.next().hasClass('child girl')) {
 children.push($row.next());
 $row = $row.next();
 }            
 return children;
 }        
 
 $('.parent').on('click', function() {
 
 var children = getChildren($(this));
 $.each(children, function() {
 $(this).toggle();
 })
 });
 
 })
 
 
 
 </script>
 </head>
 
 <body>
 <!--Write your code here-->
 
 <table id="mytable">
 <tbody>
 <tr class="parent">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="child girl">
 <td>Cash and Equivalents</td>
 <td>this is</td>
 <td>child girl</td>
 
 </tr>
 <tr class="child">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="child">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="parent">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="child">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 
 <tr class="child">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="parent">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="child">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 </tr>
 <tr class="child">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 </tr>
 </tbody>
 </table>
 
 </body>
 
 </html>
 */

/**
 * end sigle level
 */


/**
 * multi levels
 */

/*
 $(document).ready(function() {
 
 function getChildren($row) {
 var children = [], level = $row.attr('data-level');
 while($row.next().attr('data-level') > level) {
 children.push($row.next());
 $row = $row.next();
 }            
 return children;
 }        
 
 $('.parent').on('click', function() {
 
 var children = getChildren($(this));
 $.each(children, function() {
 $(this).toggle();
 })
 });
 
 })
 */
/*
 <table id="mytable">
 <tbody>
 <tr class="parent" data-level="0">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="parent" data-level="1">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="child" data-level="2">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="child" data-level="2">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="child" data-level="2">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="parent" data-level="1">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 
 <tr class="child" data-level="2">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="parent" data-level="1">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 
 </tr>
 <tr class="child" data-level="2">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 </tr>
 <tr class="child" data-level="2">
 <td>Cash and Equivalents</td>
 <td>000,000</td>
 <td>000,000</td>
 </tr>
 </tbody>
 </table>
 */
/**
 * end multi level
 */