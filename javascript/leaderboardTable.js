/*
 * Author: Erik Fox
 * Date Created: 5/22/18
 * Last Updated: 9/25/18
 */

"use strict"
var coll = document.getElementsByClassName("collapsible");
var content = document.getElementsByClassName("content");
var subcontent = document.getElementsByClassName("subcontent");
var i;

// An onClick for all collapsible rows.
for (i = 0; i < coll.length; i++) {
    coll[i].addEventListener("click", function(x,collapsibleId) {
        return function(){
            toggleContent(x,collapsibleId);
        }
    }(i,coll[i].getAttribute('name')));
}

// An onClick for all collapsible content rows.
for (i = 0; i < content.length; i++) {
    content[i].addEventListener("click", function(x,contentId,child) {
        return function(){
            toggleSubContent(x,contentId,child);
        }
    }(i,content[i].getAttribute('name'),content[i].getAttribute('child')));
}

// An onClick for all collapsible content info rows.
for (i = 0; i < subcontent.length; i++) {
    subcontent[i].addEventListener("click", function(x,contentId,child) {
        return function(){
            toggleSubContentInfo(x,contentId,child);
        }
    }(i,subcontent[i].getAttribute('name'),subcontent[i].getAttribute('child')));
}

// Collapse or expand all content on click.
function toggleContent(i, collapsibleClassName){
    var content_number = document.getElementsByName("c" + collapsibleClassName);
    for (var j = 0; j < content_number.length; j++) {
        if (content_number[j].style.visibility === 'visible') {
            content_number[j].style.visibility = 'collapse';

            coll[i].querySelectorAll(".c0 .dropdown")[0].style.transform = 'rotate(-90deg)';
        } else {
            content_number[j].style.visibility = 'visible';

            coll[i].querySelectorAll(".c0 .dropdown")[0].style.transform = 'rotate(0deg)';
        }
        var subcontent_number = document.getElementsByName("c" + collapsibleClassName + "s" + j);

        // Collapse all subcontent when collapsing content.
        for (var k = 0; k < subcontent_number.length; k++) {
            if (subcontent_number[k].style.visibility === 'visible') {
                subcontent_number[k].style.visibility = 'collapse';

                content_number[j].querySelectorAll(".c1 .dropdown")[0].style.transform = 'rotate(-90deg)';
            }

            // Collapse all subcontent info when collapsing content.
            var subcontentInfo_number = document.getElementsByName("c" + collapsibleClassName + "s" + j + "i" + k);
            for (var l = 0; l < subcontentInfo_number.length; l++) {
                if (subcontentInfo_number[l].style.visibility === 'visible') {
                    subcontentInfo_number[l].style.visibility = 'collapse';

                    subcontent_number[k].querySelectorAll(".c1 .dropdown")[0].style.transform = 'rotate(-90deg)';
                }
            }
        }
    }
}

// Collapse or expand all subcontent on click.
function toggleSubContent(i,contentClassName,child){
    var subcontent_number = document.getElementsByName(contentClassName + child);
    for (var j = 0; j < subcontent_number.length; j++) {
        if (subcontent_number[j].style.visibility === 'visible') {
            subcontent_number[j].style.visibility = 'collapse';

            content[i].querySelectorAll(".c1 .dropdown")[0].style.transform = 'rotate(-90deg)';
        } else {
            subcontent_number[j].style.visibility = 'visible';

            content[i].querySelectorAll(".c1 .dropdown")[0].style.transform = 'rotate(0deg)';
        }

        // Collapse all subcontent info when collapsing subcontent.
        var subcontentInfo_number = document.getElementsByName(contentClassName + child + "i" + j);
        for (var k = 0; k < subcontentInfo_number.length; k++) {
            if (subcontentInfo_number[k].style.visibility === 'visible') {
                subcontentInfo_number[k].style.visibility = 'collapse';

                subcontent_number[j].querySelectorAll(".c1 .dropdown")[0].style.transform = 'rotate(-90deg)';
            }
        }
    }
}

// Collapse or expand all subcontent info on click.
function toggleSubContentInfo(i,contentClassName,child){
    var subcontentInfo_number = document.getElementsByName(contentClassName + child);
    for (var j = 0; j < subcontentInfo_number.length; j++) {
        if (subcontentInfo_number[j].style.visibility === 'visible') {
            subcontentInfo_number[j].style.visibility = 'collapse';
            subcontent[i].querySelectorAll(".c1 .dropdown")[0].style.transform = 'rotate(-90deg)';
        } else {
            subcontentInfo_number[j].style.visibility = 'visible';

            subcontent[i].querySelectorAll(".c1 .dropdown")[0].style.transform = 'rotate(0deg)';
        }
    }
}