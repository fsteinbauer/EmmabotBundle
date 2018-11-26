var EmmaBot = function () {

    /**
     * Variable of the chat window should be visible.
     *
     * @type {boolean}
     */
    var chatOpened = true;

    /**
     * Timestamp when the last message was sent.
     *
     * @type {Date}
     */
    var lastMessageSent = null;

    /**
     *
     * @type {null}
     */
    var $chatMessages = null;




    var initChatbox = function() {

        $.ajax({
            method: 'GET',
            url: Routing.generate("emma_load")

        }).done(function (data) {
            if(data.success){
                $('body').append(data.html);

                if (Cookies && Cookies.get('chat_opened') === '0') {
                    $('.chat').removeClass('chat-opened');
                    chatOpened = false;
                }

                $('.chat-header').click(function (event) {
                    if(chatOpened){
                        chatOpened = false;
                        $('.chat').removeClass('chat-opened');
                        if (Cookies) {
                            Cookies.set('chat_opened', '0');
                        }

                    } else {
                        chatOpened = true;
                        $('.chat').addClass('chat-opened');
                        if (Cookies) {
                            Cookies.set('chat_opened', '1');
                        }
                    }
                });

                $chatMessages = $('.chat-history');
            }
        });
    };


    var scrollBottom = function(){
        $chatMessages.animate({scrollTop:$chatMessages.get(0).scrollHeight}, 'slow');
    };

    var addTimeToHistory = function (currentTime) {
        var timeString = currentTime.getHours()+":"+currentTime.getMinutes();

        $('.chat-history ul').append(
            $('<li class="timestamp"/>').append($('<span/>').text(timeString))
        );
    };


    var initSendMessage = function () {

        $('body').on('keyup', '#emma-chatbox', function (event) {

            if(event.keyCode === 13 && !event.shiftKey){

                var $chatbox = $(this);

                var input = $chatbox.val();
                $chatbox.val("");

                currentTime = new Date();
                if(lastMessageSent === null || (currentTime.getTime() - lastMessageSent.getTime()) > 5*60*1000){
                    addTimeToHistory(currentTime);
                    lastMessageSent = currentTime;
                }

                addMessageToHistory('other-message float-right', nl2br(input));
                addMessageToHistory('my-message message-loading', getLoadingIcon());

                scrollBottom();

                getAnswer(input);
            }
        });
    };

    /**
     *
     * @param type
     * @param input
     */
    var addMessageToHistory = function (type, input) {

        $elt = getMessageTemplate(type, input);
        $('.chat-history ul').append($elt);

    };

    var getMessageTemplate = function(cssClass, $content){

        return $('<li class="clearfix"/>').append($('<div/>').addClass("message "+cssClass).html($content));
    };

    var getLoadingIcon = function () {

      return $('<span class="chat-loading"/>').append(
          $('<span/>').append($('<i class="fa fa-circle"/>')),
          $('<span/>').append($('<i class="fa fa-circle"/>')),
          $('<span/>').append($('<i class="fa fa-circle"/>'))
      );
    };

    var getAnswer = function (input) {

        $.ajax({
            method: 'POST',
            url:    Routing.generate('emma_answer'),
            data:   {
                input:  input
            },
            success: function (data) {

                $chatMessages.find('.message-loading')
                    .html(nl2br(data.answer))
                    .removeClass('message-loading');

                scrollBottom();
            }
        })
    };


    /**
     * Converts newlines to html br tags
     *
     * @param str
     * @param is_xhtml
     * @returns {string}
     */
    var nl2br = function(str, is_xhtml) {
        if (typeof str === 'undefined' || str === null) {
            return '';
        }
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    };



    return {
        //main function to initiate the module
        init: function () {
            initChatbox();
            initSendMessage();

        }
    };

}();

jQuery(document).ready(function() {
    EmmaBot.init();
});