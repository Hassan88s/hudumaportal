<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix'=>'seller','middleware'=>['auth','inactiveuser','BuyerCheck','userEmailVerify','setlang','globalVariable']],function(){

        Route::get('/test-notify', function () {
    $user = \App\User::find(1885); // your own ID
    $user->notify(new \App\Notifications\OrderNotification(123, 5, 1885, 1904, 'Test Live Notification'));
    return 'Notification sent!';
});

///
    Route::get('/dashboard','Frontend\SellerController@sellerDashboard')->name('seller.dashboard');
    Route::get('/pushnotification','Frontend\SellerController@pushnotification')->name('seller.pushnotification');
    Route::get('/general-notification/view/{id}','Frontend\SellerController@generalpushnotificationview')->name('seller.gernel.notifications');
    
   
    Route::get('/profile','Frontend\SellerController@sellerProfile')->name('seller.profile');
    Route::match(['get','post'],'/profile-edit','Frontend\SellerController@sellerProfileEdit')->name('seller.profile.edit');
    Route::match(['get','post'],'/account-settings','Frontend\SellerController@sellerAccountSetting')->name('seller.account.settings');
    Route::post('/account-deactive','Frontend\SellerController@accountDeactive')->name('seller.account.deactive');
    Route::get('/account-deactive/cancel/{id}','Frontend\SellerController@accountDeactiveCancel')->name('seller.account.deactive.cancel');
    Route::post('account/delete','Frontend\SellerController@accountDelete')->name('seller.account.delete');
    Route::get('/logout','Frontend\SellerController@sellerLogout')->name('seller.logout');
    // custom offer
    Route::get('/Custom','Frontend\CustomController@index')->name('seller.Custom');
    Route::post('/Add-Custom','Frontend\CustomController@add_custom_offer')->name('seller.add.customoffer');
    Route::get('/offer-withdrwa/{id}','Frontend\CustomController@Withdrwal_custom_offer')->name('seller.job.withdrwa');
    Route::get('/offer-withdrwa-message/{id}','Frontend\CustomController@Withdrwal_custom_offer_message')->name('seller.job.withdrwa_message');

    ///manage portfolio
  
     Route::get('portfolio/all', 'Frontend\PortfolioController@index')->name('seller.portfolio.all');
        Route::get('portfolio/create', 'Frontend\PortfolioController@create')->name('seller.portfolio.create');
            Route::post('portfolio/store', 'Frontend\PortfolioController@store')->name('seller.portfolio.store');
             Route::post('portfolio/{id}/delete','Frontend\PortfolioController@destroy')->name('seller.portfolio.delete');
             
           Route::post('/portfolio/image/{id}', 'Frontend\PortfolioController@deleteImage')->name('seller.portfolio.image.delete');


               Route::get('portfolio/{id}/edit', 'Frontend\PortfolioController@edit')->name('seller.portfolio.edit');
    Route::post('portfolio/{id}/update','Frontend\PortfolioController@update')->name('seller.portfolio.update');
    
    ///manage project
    Route::get('project/all', 'Frontend\ProjectController@index')->name('seller.project.all');

    Route::get('project/create', 'Frontend\ProjectController@create')->name('seller.project.create');
    Route::post('project/store', 'Frontend\ProjectController@store')->name('seller.project.store');
    Route::post('project/{id}/delete','Frontend\ProjectController@destroy')->name('seller.project.delete');
    Route::post('/project/image/{id}', 'Frontend\ProjectController@deleteImage')->name('seller.project.image.delete');
    Route::get('project/{id}/edit', 'Frontend\ProjectController@edit')->name('seller.project.edit');
    Route::post('project/{id}/update','Frontend\ProjectController@update')->name('seller.project.update');
    
    
  
    //service coupons
    Route::get('/coupons','Frontend\SellerController@serviceCoupon')->name('seller.service.coupon');
    Route::post('/coupons/add-coupon','Frontend\SellerController@addServiceCoupon')->name('seller.service.coupon.add');
    Route::post('/coupons/update-coupon','Frontend\SellerController@updateServiceCoupon')->name('seller.service.coupon.update');
    Route::post('/coupons/change-status/{id}','Frontend\SellerController@changeCouponStatus')->name('seller.service.coupon.status');
    Route::post('/coupons/delete/{id}','Frontend\SellerController@couponDelete')->name('seller.service.coupon.delete');

    Route::get('/services','Frontend\SellerController@sellerServices')->name('seller.services');
    ////
    Route::get('/promoted/services/{id}','Frontend\SellerController@sellerpromotedServices')->name('seller.promoted.services');
    ////
    Route::post('/pay_promoted/services','Frontend\SellerController@payforpromoted_jobs')->name('seller.paypromoted.services');
    
    Route::get('/flutterwave/ipn','Frontend\SellerController@flutterwave_ipn_for_features')->name('seller.flutterwave.ipn.promoted');
    ////
    Route::post('/get-dependent-subcategory','Frontend\SellerController@getSubcategory')->name('seller.subcategory');
    
    //ai description
    
    Route::post('/generate-description', 'Frontend\SellerController@generateDescription');

    
    // get child category for service add
    Route::post('/get-child-category-by-subcategory', 'Frontend\SellerController@getChildCategory')->name('seller.subcategory.child.category');
    Route::match(['get','post'],'/add-services','Frontend\SellerController@addServices')->name('seller.add.services');

    Route::get('/service-attributes','Frontend\SellerController@serviceAttributes')->name('seller.services.attributes');
    Route::post('/add-service-attributes','Frontend\SellerController@addServiceAttributes')->name('seller.services.attributes.add');
    Route::match(['get','post'],'/add-service-attributes-by-id/{id?}','Frontend\SellerController@addServiceAttributesById')->name('seller.services.attributes.add.byid');
    Route::post('/service-on-of','Frontend\SellerController@ServiceOnOf')->name('seller.services.on.of');
    Route::get('/show-service-attributes-by-id/{id?}','Frontend\SellerController@showServiceAttributesById')->name('seller.services.attributes.show.byid');
    Route::post('/delete-include-service/{id?}','Frontend\SellerController@deleteIncludeService')->name('seller.services.includeservice.delete');
    Route::post('/delete-additional-service/{id?}','Frontend\SellerController@deleteAdditionalService')->name('seller.services.additionalservice.delete');
    Route::post('/delete-service-benifit/{id?}','Frontend\SellerController@deleteBenifit')->name('seller.services.benifit.delete');
    Route::post('/delete-service-faq/{id?}','Frontend\SellerController@deleteFaq')->name('seller.services.faq.delete');
    Route::post('/service-delete/{id}','Frontend\SellerController@ServiceDelete')->name('seller.services.delete');

    Route::any('/edit-services/{id?}','Frontend\SellerController@editServices')->name('seller.edit.services');
    Route::match(['get','post'],'/edit-service-attributes/{id?}','Frontend\SellerController@editServiceAttribute')->name('seller.edit.service.attribute');
    Route::match(['get','post'],'/edit-service-attributes-offline-to-online/{id?}','Frontend\SellerController@editServiceAttributeOfflineToOnline')->name('seller.edit.service.attribute.offline.to.online');

    //day
    Route::get('/days','Frontend\SellerController@days')->name('seller.days');
    Route::post('/add-day','Frontend\SellerController@addDay')->name('seller.add.day');
    Route::post('/day-delete/{id}','Frontend\SellerController@dayDelete')->name('seller.day.delete');
    Route::post('/update-total-day','Frontend\SellerController@updateTotalDay')->name('seller.update.totalday');

    //unified availability page (merges days + schedules for easier seller UX)
    Route::get('/availability','Frontend\SellerController@availability')->name('seller.availability');
    Route::post('/day-toggle-status','Frontend\SellerController@dayToggleStatus')->name('seller.day.toggle.status');

    //schedules
    Route::get('/schedules','Frontend\SellerController@schedules')->name('seller.schedules');
    Route::post('/add-schedule','Frontend\SellerController@addSchedule')->name('seller.add.schedule');
    Route::post('/edit-schedule','Frontend\SellerController@editSchedule')->name('seller.edit.schedule');
    Route::post('/schedules-delete/{id}','Frontend\SellerController@scheduleDelete')->name('seller.schedule.delete');
    Route::post('/allow/multiple/schedule/','Frontend\SellerController@allow')->name('seller.allow.multiple.schedule');

    //Services all order list
    Route::get('/orders','Frontend\SellerController@sellerOrders')->name('seller.orders');
    // job all order list
    
   
    
    Route::get('/request-location/{id}', 'Frontend\SellerController@requestLocation')->name('location.request');
    Route::get('/view-location/{id}', 'Frontend\SellerController@locationshow')->name('location.view');

    Route::get('/job-orders','Frontend\SellerController@sellerJobOrders')->name('seller.job.orders');
    
    Route::get('/orders-details/{id}','Frontend\SellerController@orderDetails')->name('seller.order.details');
    Route::post('/order-status-change','Frontend\SellerController@orderStatus')->name('seller.order.status');
    Route::post('/order-payment-status-change','Frontend\SellerController@orderPaymentStatus')->name('seller.order.payment.status');
        // partial payment
         Route::post('/order-partial-payment','Frontend\SellerController@partialpaymentRequest')->name('seller.order.partialpayment');
        
    // service orders
    Route::get('orders/active-orders','Frontend\SellerController@activeOrders')->name('seller.active.orders');
    Route::get('orders/complete-orders','Frontend\SellerController@completeOrders')->name('seller.complete.orders');
    Route::get('orders/deliver-orders','Frontend\SellerController@deliverOrders')->name('seller.deliver.orders');
    Route::get('orders/cancel-orders','Frontend\SellerController@cancelOrders')->name('seller.cancel.orders');
    
    Route::get('orders/approved-service/{id}','Frontend\SellerController@serviceorderapproved')->name('seller.approve.services');
    
    Route::get('orders/cancel-service/{id}','Frontend\SellerController@serviceorderCancel')->name('seller.cancel.services');
    ///cron

    //time
    Route::post('orders/time-extension','Frontend\SellerController@RequestTimeExtension')->name('seller.order.timeextension');
    
    //alerts
    
    Route::get('/job-alert-subscribe','Frontend\SellerController@showSubscriptionForm')->name('seller.job.alert.subscribe');
    Route::post('/job-alert-subscribe', 'Frontend\SellerController@storeSubscription')->name('job-alert.store');
    
    
    //job orders
    Route::get('orders/job/active-orders','Frontend\SellerController@activeJobOrders')->name('seller.job.active.orders');
    Route::get('orders/job/complete-orders','Frontend\SellerController@completeJobOrders')->name('seller.job.complete.orders');
    Route::get('orders/job/deliver-orders','Frontend\SellerController@deliverJobOrders')->name('seller.job.deliver.orders');
    Route::get('orders/job/cancel-orders','Frontend\SellerController@cancelJobOrders')->name('seller.job.cancel.orders');


    Route::get('pending-orders','Frontend\SellerController@pendingOrders')->name('seller.pending.orders');
    Route::post('/order-delete/{id}','Frontend\SellerController@orderDelete')->name('seller.order.delete');

    Route::post('order/report-us','Frontend\SellerController@reportUs')->name('seller.order.report');
    Route::get('order/report/list','Frontend\SellerController@reportList')->name('seller.order.report.list');
    Route::match(['get','post'],'/report/chat/to/admin/{report_id?}','Frontend\SellerController@chat_to_admin')->name('seller.order.report.chat.admin');

    Route::get('/decline-order-history/{id}','Frontend\SellerController@orderRequestDeclineHistory')->name('seller.order.request.decline.history');
    Route::post('cancel/order/if-cash-on-delivery/payment-pending/{id}','Frontend\SellerController@orderCancel')->name('seller.order.cancel.cod.payment.pending');


    /* extra order request */
    Route::post('order/extra-service','Frontend\SellerController@extraService')->name('seller.order.extra.service');
    Route::post('order/extra-service/delete','Frontend\SellerController@extraServiceDelete')->name('seller.order.extra.service.delete');

    //notifications 
    Route::get('notification/all-notifications','Frontend\SellerController@allNotification')->name('seller.notification.all');
    Route::get('clear/notifications','Frontend\SellerController@allClearMessage')->name('seller.clear.notifications');

    //payout request 
    Route::get('payout-request','Frontend\SellerController@payoutRequest')->name('seller.payout');
    Route::post('create-payout-request','Frontend\SellerController@createPayoutRequest')->name('seller.create.payout.request');
    Route::get('payout-request-details/{id?}','Frontend\SellerController@PayoutRequestDetails')->name('seller.payout.request.details');

    Route::get('payout-invoice-details/{id?}','Frontend\InvoiceController@PayoutInvoice')->name('seller.payout.invoice.details');
    Route::get('order-invoice-details/{id?}','Frontend\InvoiceController@orderInvoiceSeller')->name('seller.order.invoice.details');

    //reviews
    Route::get('service-reviews','Frontend\SellerController@serviceReview')->name('seller.service.review');
    Route::get('service-all-reviews/{id}','Frontend\SellerController@serviceReviewAll')->name('service.review.all');
    Route::post('review-delete/{id}','Frontend\SellerController@reviewDelete')->name('service.review.delete');

    // seller to buyer review
    Route::post('review/seller-to-buyer', 'Frontend\SellerController@sellerToBuyerReview')->name('seller.to.buyer.review');

    //tickets
    Route::get('all-tickets','Frontend\SellerController@allTickets')->name('seller.support.ticket');
    Route::match(['get','post'],'add-new-ticket/{id?}','Frontend\SellerController@addNewTicket')->name('seller.support.ticket.new');
    Route::post('support-ticket-delete/{id}','Frontend\SellerController@ticketDelete')->name('seller.support.ticket.delete');
    Route::post('support-ticket/priority-change/','Frontend\SellerController@priorityChange')->name('seller.support.ticket.priority.change');
    Route::post('support-ticket/status-change/{id?}','Frontend\SellerController@statusChange')->name('seller.support.ticket.status.change');
    Route::get('ticket-view/{id?}','Frontend\SellerController@view_ticket')->name('seller.support.ticket.view');
    Route::post('support-ticket/message-send', 'Frontend\SellerController@support_ticket_message')->name('seller.support.ticket.message.send');

    //service coupons
     Route::get('/to-do-list','Frontend\SellerController@toDoList')->name('seller.todolist');
     Route::post('/to-do-list/add','Frontend\SellerController@addTodolist')->name('seller.todolist.add');
     Route::post('/to-do-list/update','Frontend\SellerController@updateTodolist')->name('seller.todolist.update');
     Route::post('/to-do-list/delete/{id}','Frontend\SellerController@deleteTodolist')->name('seller.todolist.delete');
     Route::post('/to-do-list/status-change/{id?}','Frontend\SellerController@changeTodoStatus')->name('seller.todolist.status');

    //seller profile verify 
    Route::match(['get','post'],'/seller-profile-verify','Frontend\SellerController@sellerVerify')->name('seller.profile.verify');
    Route::match(['get','post'],'/account-settings','Frontend\SellerController@sellerAccountSetting')->name('seller.account.settings');


    // service zone
    Route::get('/seller-zone','Frontend\ZoneController@sellerZone')->name('seller.service.zone');
    Route::post('/service-zone-update','Frontend\ZoneController@sellerzoneUpdate')->name('seller.zone.update');
    
    
      Route::get('/start-stream','Frontend\SellerController@start_stream')->name('seller.start.stream');
     Route::post('/end-stream','Frontend\SellerController@end_stream')->name('seller.end.stream');
    
    
    //  Route::post('/zoom/create', 'Frontend\VideoSDKController@createMeeting');
    // Route::get('/zoom/signature', 'Frontend\VideoSDKController@generateSignature');
     
     

    // Route::get('/zoom/video', 'Frontend\ZoomController@index');
    // Route::post('/video/start', 'Frontend\ZoomController@startSession');
    // Route::post('/video/signature', 'Frontend\ZoomController@generateSignature');
    // Route::get('/video/join/{sessionName}', 'Frontend\ZoomController@joinSession');
    // Route::post('/video/end/{sessionName}', 'Frontend\ZoomController@endSession');

  


});

