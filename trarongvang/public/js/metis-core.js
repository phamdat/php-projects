/**
 * Metis - Bootstrap-Admin-Template v2.3.2
 * Author : onokumus
 * Copyright 2015
 * Licensed under MIT (https://github.com/onokumus/Bootstrap-Admin-Template/blob/master/LICENSE.md)
 */

;
(function (window) {
    var
    // Is Modernizr defined on the global scope
        Modernizr = typeof Modernizr !== "undefined" ? Modernizr : false,
        // whether or not is a touch device
        isTouchDevice = Modernizr ? Modernizr.touch : !!('ontouchstart' in window || 'onmsgesturechange' in window),
        // Are we expecting a touch or a click?
        buttonPressedEvent = (isTouchDevice) ? 'touchstart' : 'click',
        Metis = function () {
            this.init();
        };

    // Initialization method
    Metis.prototype.init = function () {
        this.isTouchDevice = isTouchDevice;
        this.buttonPressedEvent = buttonPressedEvent;
    };

    Metis.prototype.getViewportHeight = function () {

        var docElement = document.documentElement,
            client = docElement.clientHeight,
            inner = window.innerHeight;

        if (client < inner)
            return inner;
        else
            return client;
    };

    Metis.prototype.getViewportWidth = function () {

        var docElement = document.documentElement,
            client = docElement.clientWidth,
            inner = window.innerWidth;

        if (client < inner)
            return inner;
        else
            return client;
    };

    // Creates a Metis object.
    window.Metis = new Metis();
})(this);

(function ($) {
    "use strict";

    var $navBar = $('nav.navbar'),
        $body = $('body'),
        $menu = $('#menu');

    function addPaddingTop(el, val) {
        el.css('padding-top', val);
    }

    function removePaddingTop(el) {
        el.css('padding-top', 'inherit');
    }

    function getHeight(el) {
        return el.outerHeight();
    }

    function init() {
        var isFixedNav = $navBar.hasClass('navbar-fixed-top');
        var bodyPadTop = isFixedNav ? $navBar.outerHeight(true) : 0;

        $body.css('padding-top', bodyPadTop);

        if ($body.hasClass('menu-affix')) {
            $menu.affix({
                offset: {
                    top: $menu.offset().top
                }
            }).css({
                height: function () {
                    if ($(window).width() < 768) {
                        return $(window).height();
                    } else {
                        return $(window).height();
                    }
                },
                top: bodyPadTop - 1,
                bottom: 0
            });
            console.log($navBar.outerHeight(true));
        }
    }

    Metis.navBar = function () {
        var resizeTimer;
        init();
        $(window).resize(function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(init(), 250);
        });
    };
    return Metis;
})(jQuery);

(function ($, Metis) {
    "use strict";
    // Define toggleFullScreen
    Metis.toggleFullScreen = function () {
        if ((window.screenfull !== undefined) && screenfull.enabled) {
            $('#toggleFullScreen').on(Metis.buttonPressedEvent, function (e) {
                screenfull.toggle(window.document[0]);
                $('body').toggleClass('fullScreen');
                e.preventDefault();
            });
        } else {
            $('#toggleFullScreen').addClass('hidden');
        }
    };
    // Define boxFullScreen
    Metis.boxFullScreen = function () {
        if ((window.screenfull !== undefined) && screenfull.enabled) {
            $('.full-box').on(Metis.buttonPressedEvent, function (e) {
                var $toggledPanel = $(this).parents('.box')[0];
                screenfull.toggle($toggledPanel);
                $(this).parents('.box').toggleClass('full-screen-box');
                $(this).parents('.box').children('.body').toggleClass('full-screen-box');
                $(this).children('i').toggleClass('fa-compress');
                e.preventDefault();
            });
        } else {
            $('.full-box').addClass('hidden');
        }
    };
    Metis.panelBodyCollapse = function () {
        var $collapseButton = $('.collapse-box'),
            $collapsedPanelBody = $collapseButton.closest('.box').children('.body');

        $collapsedPanelBody.collapse('show');

        $collapseButton.on(Metis.buttonPressedEvent, function (e) {
            var $collapsePanelBody = $(this).closest('.box').children('.body'),
                $toggleButtonImage = $(this).children('i');
            $collapsePanelBody.on('show.bs.collapse', function () {
                $toggleButtonImage.removeClass('fa-minus fa-plus').addClass('fa-spinner fa-spin');
            });
            $collapsePanelBody.on('shown.bs.collapse', function () {
                $toggleButtonImage.removeClass('fa-spinner fa-spin').addClass('fa-minus');
            });

            $collapsePanelBody.on('hide.bs.collapse', function () {
                $toggleButtonImage.removeClass('fa-minus fa-plus').addClass('fa-spinner fa-spin');
            });

            $collapsePanelBody.on('hidden.bs.collapse', function () {
                $toggleButtonImage.removeClass('fa-spinner fa-spin').addClass('fa-plus');
            });

            $collapsePanelBody.collapse('toggle');

            e.preventDefault();
        });
    };
    Metis.boxHiding = function () {
        $('.close-box').on(Metis.buttonPressedEvent, function () {
            $(this).closest('.box').hide('slow');
        });
    };
    return Metis;
})(jQuery, Metis || {});

(function ($, Metis) {

    Metis.metisAnimatePanel = function () {
        var $body = $('body'),
            $leftToggle = $('.toggle-left'),
            $rightToggle = $('.toggle-right'),
            $count = 0;

        if ($('#left').length) {
            $leftToggle.on(Metis.buttonPressedEvent, function (e) {

                if ($(window).width() < 768) {
                    $body.toggleClass('sidebar-left-opened');
                } else {
                    switch (true) {
                    case $body.hasClass("sidebar-left-hidden"):
                        $("#left").show(); /*This is to display left menu/**/
                        $body.removeClass("sidebar-left-hidden sidebar-left-mini");
                        break;
                    case $body.hasClass("sidebar-left-mini"):
                        $("#left").hide(); /*To prevent wierd display of text when trying to hide menu from mini/**/
                        $body.removeClass("sidebar-left-mini").addClass("sidebar-left-hidden");
                        break;
                    default:
                        $("#left").show(); /*This is to display left menu/**/
                        $body.addClass("sidebar-left-mini");
                    }

                    e.preventDefault();
                }
            });
        } else {
            $leftToggle.addClass('hidden');
        }
        if ($('#right').length) {
            $rightToggle.on(Metis.buttonPressedEvent, function (e) {
                switch (true) {
                    // Close right panel
                case $body.hasClass("sidebar-right-opened"):
                    $body.removeClass("sidebar-right-opened");
                    break;
                default:
                    // Open right panel
                    $body.addClass("sidebar-right-opened");
                    if (!$body.hasClass("sidebar-left-mini") & !$body.hasClass("sidebar-left-hidden")) {
                        $body.addClass("sidebar-left-mini");
                    }
                }
                e.preventDefault();
            });
        } else {
            $rightToggle.addClass('hidden');
        }
    };
    return Metis;
})(jQuery, Metis || {});

(function ($, Metis) {
    var $button = $('.inner a.btn');
    Metis.metisButton = function () {
        $.each($button, function () {
            $(this).popover({
                placement: 'bottom',
                title: this.innerHTML,
                content: this.outerHTML,
                trigger: (Metis.isTouchDevice) ? 'touchstart' : 'hover'
            });
        });
    };
    return Metis;
})(jQuery, Metis || {});

(function ($, Metis) {
    "use strict";


    var d2 = [
          [0, 3],
          [1, 8],
          [2, 5],
          [3, 13],
          [4, 1]
        ],
        d3 = [
          [0, 12],
          [2, 2],
          [3, 9],
          [4, 4]
        ],
        parabola = [],
        parabola2 = [],
        circle = [],
        heartA = [],
        bernoulliA = [],
        human = $("#human"),
        eye = $("#eye"),
        bar = $("#bar"),
        heart = $("#heart"),
        bernoilli = $("#bernoilli");

    function lemniscatex(i) {
        return Math.sqrt(2) * Math.cos(i) / (Math.pow(Math.sin(i), 2) + 1);
    }

    function lemniscatey(i) {
        return Math.sqrt(2) * Math.cos(i) * Math.sin(i) / (Math.pow(Math.sin(i), 2) + 1);
    }
    Metis.MetisChart = function () {
        // Plugin check
        if (!$().plot) {
            throw new Error('flot plugin require form MetisChart');
        }
        // Human charts
        $.plot(human, [
            {
                data: d2,
                label: 'MAN'
            },
            {
                data: d3,
                label: 'WOMAN'
            }
        ], {
            clickable: true,
            hoverable: true,
            series: {
                lines: {
                    show: true,
                    fill: true,
                    fillColor: {
                        colors: [
                            {
                                opacity: 0.5
                            },
                            {
                                opacity: 0.15
                            }
                ]
                    }
                },
                points: {
                    show: true
                }
            }
        });

        // BAR charts
        $.plot(bar, [{
            data: d2,
            label: 'BAR'
        }], {
            clickable: true,
            hoverable: true,
            series: {
                bars: {
                    show: true,
                    barWidth: 0.6
                },
                points: {
                    show: true
                }
            }
        });

        // EYE charts
        for (var i = -5; i <= 5; i += 0.5) {
            parabola.push([i, Math.pow(i, 2) - 25]);
            parabola2.push([i, -Math.pow(i, 2) + 25]);
        }

        for (var c = -2; c <= 2.1; c += 0.1) {
            circle.push([c, Math.sqrt(400 - c * c * 100)]);
            circle.push([c, -Math.sqrt(400 - c * c * 100)]);
        }

        $.plot(eye, [
            {
                data: parabola2,
                lines: {
                    show: true,
                    fill: true
                }
            },
            {
                data: parabola,
                lines: {
                    show: true,
                    fill: true
                }
            },
            {
                data: circle,
                lines: {
                    show: true
                }
            }
        ]);

        // HEART charts
        for (i = -2; i <= 5; i += 0.01) {
            heartA.push([16 * Math.pow(Math.sin(i), 3), 13 * Math.cos(i) - 5 * Math.cos(2 * i) - 2 * Math.cos(3 * i) - Math.cos(4 * i)]);
        }
        $.plot($("#heart"), [
            {
                data: heartA,
                label: '<i class="fa fa-heart"></i>',
                color: '#9A004D'
            }
        ], {
            series: {
                lines: {
                    show: true,
                    fill: true
                },
                points: {
                    show: false
                }
            },
            yaxis: {
                show: true
            },
            xaxis: {
                show: true
            }
        });
        $('#heart .legendLabel').addClass('animated pulse');
        setInterval(function () {
            $('#heart .legendLabel .fa.fa-heart').toggleClass('fa-2x');
        }, 400);

        // BERNOILLI charts
        for (var k = 0; k <= 2 * Math.PI; k += 0.01) {
            bernoulliA.push([lemniscatex(k), lemniscatey(k)]);
        }
        $.plot($("#bernoilli"), [
            {
                data: bernoulliA,
                label: 'Lemniscate of Bernoulli',
                lines: {
                    show: true,
                    fill: true
                }
            }
      ]);
    }
    return Metis;
})(jQuery, Metis || {});

(function ($) {
    "use strict";
    Metis.dashboard = function () {


        //----------- BEGIN SPARKLINE CODE -------------------------*/
        // required jquery.sparkline.min.js*/

        /** This code runs when everything has been loaded on the page */
        /* Inline sparklines take their values from the contents of the tag */
        $('.inlinesparkline').sparkline();

        /* Sparklines can also take their values from the first argument
         passed to the sparkline() function */
        var myvalues = [10, 8, 5, 7, 4, 4, 1];
        $('.dynamicsparkline').sparkline(myvalues);

        /* The second argument gives options such as chart type */
        $('.dynamicbar').sparkline(myvalues, {
            type: 'bar',
            barColor: 'green'
        });

        /* Use 'html' instead of an array of values to pass options
         to a sparkline with data in the tag */
        $('.inlinebar').sparkline('html', {
            type: 'bar',
            barColor: 'red'
        });


        $(".sparkline.bar_week").sparkline([5, 6, 7, 2, 0, -4, -2, 4], {
            type: 'bar',
            height: '40',
            barWidth: 5,
            barColor: '#4d6189',
            negBarColor: '#a20051'
        });

        $(".sparkline.line_day").sparkline([5, 6, 7, 9, 9, 5, 4, 6, 6, 4, 6, 7], {
            type: 'line',
            height: '40',
            drawNormalOnTop: false
        });

        $(".sparkline.pie_week").sparkline([1, 1, 2], {
            type: 'pie',
            width: '40',
            height: '40'
        });

        $('.sparkline.stacked_month').sparkline(['0:2', '2:4', '4:2', '4:1'], {
            type: 'bar',
            height: '40',
            barWidth: 10,
            barColor: '#4d6189',
            negBarColor: '#a20051'
        });
        //----------- END SPARKLINE CODE -------------------------*/


        //----------- BEGIN FULLCALENDAR CODE -------------------------*/

        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        var calendar = $('#calendar').fullCalendar({
            header: {
                left: 'prev,today,next,',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            selectable: true,
            selectHelper: true,
            select: function (start, end, allDay) {
                var title = prompt('Event Title:');
                if (title) {
                    calendar.fullCalendar('renderEvent', {
                            title: title,
                            start: start,
                            end: end,
                            allDay: allDay
                        },
                        true // make the event "stick"
                    );
                }
                calendar.fullCalendar('unselect');
            },
            editable: true,
            events: [
                {
                    title: 'All Day Event',
                    start: new Date(y, m, 1),
                    className: 'label label-success'
            },
                {
                    title: 'Long Event',
                    start: new Date(y, m, d - 5),
                    end: new Date(y, m, d - 2),
                    className: 'label label-info'
            },
                {
                    id: 999,
                    title: 'Repeating Event',
                    start: new Date(y, m, d - 3, 16, 0),
                    allDay: false,
                    className: 'label label-warning'
            },
                {
                    id: 999,
                    title: 'Repeating Event',
                    start: new Date(y, m, d + 4, 16, 0),
                    allDay: false,
                    className: 'label label-inverse'
            },
                {
                    title: 'Meeting',
                    start: new Date(y, m, d, 10, 30),
                    allDay: false,
                    className: 'label label-important'
            },
                {
                    title: 'Lunch',
                    start: new Date(y, m, d, 12, 0),
                    end: new Date(y, m, d, 14, 0),
                    allDay: false
            },
                {
                    title: 'Birthday Party',
                    start: new Date(y, m, d + 1, 19, 0),
                    end: new Date(y, m, d + 1, 22, 30),
                    allDay: false
            },
                {
                    title: 'Click for Google',
                    start: new Date(y, m, 28),
                    end: new Date(y, m, 29),
                    url: 'http://google.com/'
            }
        ]
        });
        /*----------- END FULLCALENDAR CODE -------------------------*/



        /*----------- BEGIN CHART CODE -------------------------*/
        var sin = [],
            cos = [];
        for (var i = 0; i < 14; i += 0.5) {
            sin.push([i, Math.sin(i)]);
            cos.push([i, Math.cos(i)]);
        }

        var plot = $.plot($("#trigo"), [
            {
                data: sin,
                label: "sin(x)",
                points: {
                    fillColor: "#4572A7",
                    size: 5
                },
                color: '#4572A7'
                },
            {
                data: cos,
                label: "cos(x)",
                points: {
                    fillColor: "#333",
                    size: 35
                },
                color: '#AA4643'
                }
            ], {
            series: {
                lines: {
                    show: true
                },
                points: {
                    show: true
                }
            },
            grid: {
                hoverable: true,
                clickable: true
            },
            yaxis: {
                min: -1.2,
                max: 1.2
            }
        });

        function showTooltip(x, y, contents) {
            $('<div id="tooltip">' + contents + '</div>').css({
                position: 'absolute',
                display: 'none',
                top: y + 5,
                left: x + 5,
                border: '1px solid #fdd',
                padding: '2px',
                'background-color': '#000',
                color: '#fff'
            }).appendTo("body").fadeIn(200);
        }

        var previousPoint = null;
        $("#trigo").bind("plothover", function (event, pos, item) {
            $("#x").text(pos.x.toFixed(2));
            $("#y").text(pos.y.toFixed(2));

            if (item) {
                if (previousPoint !== item.dataIndex) {
                    previousPoint = item.dataIndex;

                    $("#tooltip").remove();
                    var x = item.datapoint[0].toFixed(2),
                        y = item.datapoint[1].toFixed(2);

                    showTooltip(item.pageX, item.pageY,
                        item.series.label + " of " + x + " = " + y);
                }
            } else {
                $("#tooltip").remove();
                previousPoint = null;
            }
        });
        /*----------- END CHART CODE -------------------------*/

        /*----------- BEGIN TABLESORTER CODE -------------------------*/
        /* required jquery.tablesorter.min.js*/
        $(".sortableTable").tablesorter();
        /*----------- END TABLESORTER CODE -------------------------*/

    };
    return Metis;
})(jQuery);

(function ($) {
    "use strict";

    Metis.formGeneral = function () {

        $('.with-tooltip').tooltip({
            selector: ".input-tooltip"
        });

        /*----------- BEGIN inputlimiter CODE -------------------------*/
        $('.input-limiter').each(function () {
            $(this).inputlimiter({
                limit: $(this).attr('maxlength'),
                remText: 'You only have %n character%s remaining...',
                limitText: 'You\'re allowed to input %n character%s into this field.'
            });
        });
        /*----------- END inputlimiter CODE -------------------------*/

        /*----------- BEGIN tagsInput CODE -------------------------*/
        $('.input-tags').tagsInput();
        /*----------- END tagsInput CODE -------------------------*/

        /*----------- BEGIN chosen CODE -------------------------*/
        $(".input-multiple-select").chosen();
        /*----------- END chosen CODE -------------------------*/

        /*----------- BEGIN ckeditor CODE -------------------------*/
        $('.input-editor').ckeditor();
        /*----------- END ckeditor CODE -------------------------*/
    };

    return Metis;
})(jQuery);

(function ($) {
    "use strict";

    Metis.formValidation = function () {
        /*----------- BEGIN validationEngine CODE -------------------------*/
        $('.validation-form').validationEngine();
        /*----------- END validationEngine CODE -------------------------*/
    };

    return Metis;
})(jQuery);

(function ($, Metis) {
    "use strict";

    Metis.formWizard = function () {

        /*----------- BEGIN uniform CODE -------------------------*/
        $('#fileUpload').uniform();
        /*----------- END uniform CODE -------------------------*/

        /*----------- BEGIN plupload CODE -------------------------*/
        $("#uploader").pluploadQueue({
            runtimes: 'html5,html4',
            url: 'form-wysiwyg.html',
            max_file_size: '128kb',
            unique_names: true,
            filters: [
                {
                    title: "Image files",
                    extensions: "jpg,gif,png"
            }
        ]
        });
        /*----------- END plupload CODE -------------------------*/

        /*----------- BEGIN formwizard CODE -------------------------*/
        $("#wizardForm").formwizard({
            formPluginEnabled: true,
            validationEnabled: true,
            focusFirstInput: true,
            formOptions: {
                beforeSubmit: function (data) {
                    $.gritter.add({
                        // (string | mandatory) the heading of the notification
                        title: 'data sent to the server',
                        // (string | mandatory) the text inside the notification
                        text: $.param(data),
                        sticky: false
                    });

                    return false;
                },
                dataType: 'json',
                resetForm: true
            },
            validationOptions: {
                rules: {
                    server_host: "required",
                    server_name: "required",
                    server_user: "required",
                    server_password: "required",
                    table_prefix: "required",
                    table_collation: "required",
                    username: {
                        required: true,
                        minlength: 3
                    },
                    usermail: {
                        required: true,
                        email: true
                    },
                    pass: {
                        required: true,
                        minlength: 6
                    },
                    pass2: {
                        required: true,
                        minlength: 6,
                        equalTo: "#pass"
                    }
                },
                errorClass: 'help-block',
                errorElement: 'span',
                highlight: function (element, errorClass, validClass) {
                    $(element).parents('.form-group').removeClass('has-success').addClass('has-error');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).parents('.form-group').removeClass('has-error').addClass('has-success');
                }
            }
        });
        /*----------- END formwizard CODE -------------------------*/

    };

    return Metis;
})(jQuery, Metis || {});

(function ($) {
    "use strict";
    Metis.formWysiwyg = function () {

        /*----------- BEGIN wysihtml5 CODE -------------------------*/
        $('#wysihtml5').wysihtml5();
        /*----------- END wysihtml5 CODE -------------------------*/

        /*----------- BEGIN Markdown.Editor CODE -------------------------*/
        var converter = Markdown.getSanitizingConverter();
        var editor = new Markdown.Editor(converter);
        editor.run();
        /*----------- END Markdown.Editor CODE -------------------------*/

        // DEPRECATED
        //     /*----------- BEGIN cleditor CODE -------------------------*/
        //     var cleditor = $("#cleditor").cleditor({width: "100%", height: "100%"})[0].focus();
        //     $(window).resize();
        //
        //     $(window).resize(function () {
        //         var $win = $('#cleditorDiv');
        //         cleditor.width($win.width() - 24).height($win.height() - 24).offset({
        //             left: 15,
        //             top: 15
        //         });
        //         editor.refresh();
        //     });
        //     /*----------- END cleditor CODE -------------------------*/

        /*----------- BEGIN epiceditor CODE -------------------------*/
        var opts = {
            basePath: '//cdnjs.cloudflare.com/ajax/libs/epiceditor/0.2.2'
        };
        var epiceditor = new EpicEditor(opts).load();
        /*----------- END epiceditor CODE -------------------------*/
    };
    return Metis;
})(jQuery);

;
(function ($) {
    "use strict";
    Metis.MetisCalendar = function () {
        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        var hdr = {};

        if ($(window).width() <= 767) {
            hdr = {
                left: 'title',
                center: 'month,agendaWeek,agendaDay',
                right: 'prev,today,next'
            };
        } else {
            hdr = {
                left: '',
                center: 'title',
                right: 'prev,today,month,agendaWeek,agendaDay,next'
            };
        }

        var initDrag = function (e) {
            // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
            // it doesn't need to have a start or end



            var eventObject = {
                title: $.trim(e.text()), // use the element's text as the event title

                className: $.trim(e.children('span').attr('class')) // use the element's children as the event class
            };
            // store the Event Object in the DOM element so we can get to it later
            e.data('eventObject', eventObject);

            // make the event draggable using jQuery UI
            e.draggable({
                zIndex: 999,
                revert: true, // will cause the event to go back to its
                revertDuration: 0 //  original position after the drag
            });
        };

        var addEvent = function (title, priority) {
            title = title.length === 0 ? "Untitled Event" : title;

            priority = priority.length === 0 ? "label label-default" : priority;

            var html = $('<li class="external-event"><span class="' + priority + '">' + title + '</span></li>');

            jQuery('#external-events').append(html);
            initDrag(html);
        };

        /* initialize the external events
         -----------------------------------------------------------------*/

        $('#external-events li.external-event').each(function () {
            initDrag($(this));
        });

        $('#add-event').click(function () {
            var title = $('#title').val();
            var priority = $('input:radio[name=priority]:checked').val();

            addEvent(title, priority);
        });
        /* initialize the calendar
         -----------------------------------------------------------------*/

        $('#calendar').fullCalendar({
            header: hdr,
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar !!!
            drop: function (date) { // this function is called when something is dropped

                // retrieve the dropped element's stored Event Object
                var originalEventObject = $(this).data('eventObject');

                // we need to copy it, so that multiple events don't have a reference to the same object
                var copiedEventObject = $.extend({}, originalEventObject);

                // assign it the date that was reported
                copiedEventObject.start = date;

                // render the event on the calendar
                // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);


                // is the "remove after drop" checkbox checked?
                if ($('#drop-remove').is(':checked')) {
                    // if so, remove the element from the "Draggable Events" list
                    $(this).remove();
                }

            },
            windowResize: function (event, ui) {
                $('#calendar').fullCalendar('render');
            }
        });
    };
    return Metis;
})(jQuery);

(function ($) {
    "use strict";
    Metis.MetisFile = function () {

        /*----------- BEGIN elfinder CODE -------------------------*/
        var elf = $('#elfinder').elfinder({
            url: 'assets/elfinder-2.0-rc1/php/connector.php' // connector URL (REQUIRED)
                // lang: 'de',             // language (OPTIONAL)
        }).elfinder('instance');
        /*----------- END elfinder CODE -------------------------*/

    };
    return Metis;
})(jQuery);

(function ($) {
    "use strict";
    Metis.MetisMaps = function () {
        var map1, map2, map3, map4, map5, map6, path, addressMap;

        map1 = new GMaps({
            el: '#gmaps-basic',
            lat: -12.043333,
            lng: -77.028333,
            zoomControl: true,
            zoomControlOpt: {
                style: 'SMALL',
                position: 'TOP_LEFT'
            },
            panControl: false,
            streetViewControl: false,
            mapTypeControl: false,
            overviewMapControl: false
        });

        map2 = new GMaps({
            el: '#gmaps-marker',
            lat: -12.043333,
            lng: -77.028333
        });
        map2.addMarker({
            lat: -12.043333,
            lng: -77.03,
            title: 'Lima',
            details: {
                database_id: 42,
                author: 'HPNeo'
            },
            click: function (e) {
                if (console.log)
                    console.log(e);
                alert('You clicked in this marker');
            },
            mouseover: function (e) {
                if (console.log)
                    console.log(e);
            }
        });
        map2.addMarker({
            lat: -12.042,
            lng: -77.028333,
            title: 'Marker with InfoWindow',
            infoWindow: {
                content: '<p>HTML Content</p>'
            }
        });

        map3 = new GMaps({
            el: '#gmaps-geolocation',
            lat: -12.043333,
            lng: -77.028333
        });

        GMaps.geolocate({
            success: function (position) {
                map3.setCenter(position.coords.latitude, position.coords.longitude);
            },
            error: function (error) {
                alert('Geolocation failed: ' + error.message);
            },
            not_supported: function () {
                alert("Your browser does not support geolocation");
            },
            always: function () {
                //alert("Done!");
            }
        });

        map4 = new GMaps({
            el: '#gmaps-polylines',
            lat: -12.043333,
            lng: -77.028333,
            click: function (e) {
                console.log(e);
            }
        });

        path = [[-12.044012922866312, -77.02470665341184], [-12.05449279282314, -77.03024273281858], [-12.055122327623378, -77.03039293652341], [-12.075917129727586, -77.02764635449216], [-12.07635776902266, -77.02792530422971], [-12.076819390363665, -77.02893381481931], [-12.088527520066453, -77.0241058385925], [-12.090814532191756, -77.02271108990476]];

        map4.drawPolyline({
            path: path,
            strokeColor: '#131540',
            strokeOpacity: 0.6,
            strokeWeight: 6
        });

        map5 = new GMaps({
            el: '#gmaps-route',
            lat: -12.043333,
            lng: -77.028333
        });
        map5.drawRoute({
            origin: [-12.044012922866312, -77.02470665341184],
            destination: [-12.090814532191756, -77.02271108990476],
            travelMode: 'driving',
            strokeColor: '#131540',
            strokeOpacity: 0.6,
            strokeWeight: 6
        });

        addressMap = new GMaps({
            el: '#gmaps-geocoding',
            lat: -12.043333,
            lng: -77.028333
        });
        $('#geocoding_form').submit(function (e) {
            e.preventDefault();
            GMaps.geocode({
                address: $('#address').val().trim(),
                callback: function (results, status) {
                    if (status === 'OK') {
                        var latlng = results[0].geometry.location;
                        addressMap.setCenter(latlng.lat(), latlng.lng());
                        addressMap.addMarker({
                            lat: latlng.lat(),
                            lng: latlng.lng()
                        });
                    }
                }
            });
        });
    };
    return Metis;
})(jQuery);

(function ($, Metis) {
    if (!$().sortable) {
        return;
    }
    var $sortable = $('.inner [class*=col-]');
    Metis.metisSortable = function () {
        $sortable.sortable({
            placeholder: "ui-state-highlight"
        }).disableSelection();
    };
    return Metis;
})(jQuery, Metis || {});

(function ($) {
    "use strict";

    Metis.MetisTable = function () {

        /*----------- BEGIN TABLESORTER CODE -------------------------*/
        /* required jquery.tablesorter.min.js*/
        $(".sortable-table").tablesorter();
        /*----------- END TABLESORTER CODE -------------------------*/

        /*----------- BEGIN datatable CODE -------------------------*/
        $('.data-table').dataTable({
            order: [[ 0, "desc" ]]
            //         "sDom": "<'pull-right'l>t<'row'<'col-lg-6'f><'col-lg-6'p>>",
            //         "sPaginationType": "bootstrap",
            //         "oLanguage": {
            //             "sLengthMenu": "Show _MENU_ entries"
            //         }
        });
        /*----------- END datatable CODE -------------------------*/
    };

    return Metis;
})(jQuery);

(function ($, Metis) {
    "use strict";
    var _updateClass = function (el, c) {
        el.removeClass("primary success danger warning info default").addClass(c);
    };
    Metis.MetisPricing = function () {
        var $dark = $("ul.dark li.active"),
            $light = $("ul#light li.active");

        $("#dark-toggle label").on(Metis.buttonPressedEvent, function () {
            var $this = $(this);
            _updateClass($dark, $this.find("input").val());
        });

        $("#light-toggle label").on(Metis.buttonPressedEvent, function () {
            var $this = $(this);
            _updateClass($light, $this.find("input").val());
        });
    };
    return Metis;
})(jQuery, Metis || {});

(function ($, Metis) {
    Metis.MetisProgress = function () {
        var $bar = $('.progress .progress-bar');
        $.each($bar, function () {

            var $this = $(this);

            $this
                .animate({
                    width: $(this).attr('aria-valuenow') + '%'
                })
                .popover({
                    placement: 'bottom',
                    title: 'Source',
                    content: this.outerHTML
                });
        });
    };
    return Metis;
})(jQuery, Metis);

(function ($, Metis) {
    Metis.nestable = function () {
        var $nestable = $('.nestable-list'),
            $action = $('.nestable-list .dd-handle a');
        
        $nestable.each(function(index, value){
            $(this).nestable({
                rootClass: 'nestable-list',
                maxDepth: $(this).attr('level')
            });
        });        
        
        $nestable.on('change', function() {
            var params = [];
            $nestable.find('li').each(function(index, value){
                params.push({
                    'id': $(value).attr('data-id'),
                    'order_id': $nestable.find('li').length - index
                });
            });
            $.ajax({
                url: $nestable.attr('url'),
                data: {
                    'data' : JSON.stringify(params)
                },
                method: 'POST',
                success: function(){
                
                }
            });
        });
        
        $action.on('mousedown', function(event) {
            event.preventDefault();
            return false;
        });

        $action.on('click', function(event){
            event.preventDefault();
            window.location.href = $(this).attr('href');
            return false;
        });
    };
    return Metis;
})(jQuery, Metis);

(function ($) {
    $(document).ready(function () {

        $('[data-toggle="tooltip"]').tooltip();

        $('#menu').metisMenu();
        
        Metis.navBar();
        Metis.metisAnimatePanel();
        Metis.toggleFullScreen();
        Metis.boxFullScreen();
        Metis.panelBodyCollapse();
        Metis.boxHiding();
        Metis.MetisTable();
        Metis.formValidation();
        Metis.formGeneral();
        Metis.nestable();
    });
    
    $(window).load(function(){
        $(window).resize(function () {
            $('#wrap').css('min-height', $('html').height() + 'px');
            $('#content-container>.outer>.inner').css('min-height', $('html').height() - $('#content-container')[0].offsetTop - 20 + 'px');
        });
        $(window).resize();
    });
})(jQuery);