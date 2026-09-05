@extends('backend.admin-master')
@section('style')
    @include('backend.partials.datatable.style-enqueue')
@endsection
@section('site-title')
    {{__('All Email Templates')}}
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="margin-top-40"></div>
                <x-msg.success/>
                <x-msg.error/>
            </div>
            <div class="col-lg-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{__('All Email Templates')}}</h4>
                        <div class="table-wrap table-responsive">
                            <table class="table table-default" >
                                <thead>
                                <th>{{__('SN')}}</th>
                                <th>{{__('Title')}}</th>
                                <th>{{__('Action')}}</th>
                                </thead>
                                <tbody>
                                    
                                    
                                  <tr>
                                    <td><strong>Latest</strong></td>
                                    <td>
                                        {{__('Jobs Subscription Newsletter ')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for seller when they subscribe the newsletter for job.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.email.user.jobsnewsletter.template')"/>
                                    </td>
                                </tr>    
                                    
                                <tr>
                                    <td><strong>1</strong></td>
                                    <td>
                                        {{__('Seller  Register Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only Seller  Register.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.email.user.register.template')"/>
                                    </td>
                                </tr>
                                
                               
                                <tr>
                                    <td><strong>2</strong></td>
                                    <td>{{__('Seller Buyer Email Verify Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Seller and Buyer Register E-mail Verify.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.email.user.verify.template')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>3</strong></td>
                                    <td>{{__('New  Service Approve Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Seller New Service Approval.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.service.approve')"/>
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>4</strong></td>
                                    <td>{{__('New Seller Service Pending Approvel Template .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Seller New Service Approval.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.service.pendingapprove')"/>
                                    </td>
                                </tr>
                                   <tr>
                                    <td><strong>5</strong></td>
                                    <td>{{__('New Message .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Seller and buyer New Message Approval.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.new.message')"/>
                                    </td>
                                </tr>
                                  <tr>
                                    <td><strong>6</strong></td>
                                    <td>{{__('Booking approved by freelancer .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the buyer when booking Booking approved by freelancer .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.booking.approved')"/>
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>7</strong></td>
                                    <td>{{__('Custom offer sent .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller when Custom offer sent .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.customoffer.sent')"/>
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>8</strong></td>
                                    <td>{{__('Custom Offer Accepted .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller when Custom Offer Accepted .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.customoffer.Accepted')"/>
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>9</strong></td>
                                    <td>{{__('Custom Offer Declined .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller when Custom Offer Declined .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.customoffer.Declined')"/>
                                    </td>
                                </tr>
                                
                                 <tr>
                                    <td><strong>10</strong></td>
                                    <td>{{__('Applying for a job  .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller Applying for a job.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.applyjob')"/>
                                    </td>
                                </tr>
                                
                                 <tr>
                                    <td><strong>11</strong></td>
                                    <td>{{__('Job hiring.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller Job hiring .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.jobhiring')"/>
                                    </td>
                                </tr>
                                 <!--12 t0 20-->
                                 <tr>
                                    <td><strong>12</strong></td>
                                    <td>{{__('Service completion request.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller Service completion request .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.service.completion')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>13</strong></td>
                                    <td>{{__('Request Modification.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('Request Modification from buyer to seller.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.request.modification')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>14</strong></td>
                                    <td>{{__('Client approves service completion request.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller when Client approves service completion request. .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.approves.service.completion')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>15</strong></td>
                                    <td>{{__('Delivery time extension request.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the buyer when seller make Delivery time extension request .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.delivery.time.extension.request')"/>
                                    </td>
                                </tr>
                                
                                <!--///-->
                                
                                <tr>
                                    <td><strong>16</strong></td>
                                    <td>{{__('Delivery time extension request approved.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller when buyer approved Delivery time extension request.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.delivery.time.extension.approved')"/>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>17</strong></td>
                                    <td>{{__('Delivery time extension request Declined.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller when buyer Declined Delivery time extension request .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.delivery.time.extension.Declined')"/>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>18</strong></td>
                                    <td>{{__('Additional service request.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the seller Additional service request .') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.additional.service.request')"/>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>19</strong></td>
                                    <td>{{__('Additional service request approved .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the buyer when  seller approved Additional service request .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.seller.additional.service.request.approved')"/>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>20</strong></td>
                                    <td>{{__('Additional service request declined.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the buyer when  seller declined Additional service request .') }}</span>
                                    </td>
                                    <td>
                                       <x-edit-icon :url="route('admin.seller.additional.service.request.declined')"/>
                                    </td>
                                </tr>
                                <!---->
                                
                                
                                
                                
                                <!--// 1 to 7-->
                                
                                <tr>
                                    <td><strong>21</strong></td>
                                    <td>{{__('Freelancer promotes service .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Freelancer  when  Freelancer promotes service .') }}</span>
                                    </td>
                                    <td>
                                       <x-edit-icon :url="route('admin.seller.promotes.service.template')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>22</strong></td>
                                    <td>{{__('Partial payment request.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Freelancer Partial payment request .') }}</span>
                                    </td>
                                    <td>
                                       <x-edit-icon :url="route('admin.seller.partial.payment.request.template')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>23</strong></td>
                                    <td>{{__('Partial Paymnt approved.')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Freelancer Partial payment request Approved .') }}</span>
                                    </td>
                                    <td>
                                       <x-edit-icon :url="route('admin.seller.partial.payment.request.approved.template')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>24</strong></td>
                                    <td>{{__('Partial Paymnt declined .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Freelancer Partial payment request declined .') }}</span>
                                    </td>
                                    <td>
                                       <x-edit-icon :url="route('admin.seller.partial.payment.request.declined.template')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>25</strong></td>
                                    <td>{{__('Requests payout .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Requests payout.') }}</span>
                                    </td>
                                    <td>
                                       <x-edit-icon :url="route('admin.seller.payout.request.template')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>26</strong></td>
                                    <td>{{__('Payment sent .')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the buyer when  seller Payment sent .') }}</span>
                                    </td>
                                    <td>
                                       <x-edit-icon :url="route('admin.seller.payment.sent.template')"/>
                                    </td>
                                </tr>
                                <!--end-->
                                
                                
                                
                                
                                <tr>
                                    <td><strong>27</strong></td>
                                    <td>{{__('Seller Report Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Seller Report.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.report')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>28</strong></td>
                                    <td>{{__('Seller Payout Request Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Seller Create Payout Request.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.payout.request')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>29</strong></td>
                                    <td>{{__('Seller Order Ticket Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Seller Order Ticket.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.order.ticket')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>30</strong></td>
                                    <td>{{__('Seller Verification Request Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Seller Verification Request.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.verification')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>31</strong></td>
                                    <td>{{__('Seller Buyer Extra Service Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Seller and Buyer Extra Service.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.extra.service')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>32</strong></td>
                                    <td>{{__('Buyer Order Decline Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Buyer Order Decline.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.buyer.order.decline')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>33</strong></td>
                                    <td>{{__('Buyer Report Template')}} <br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Buyer Report.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.buyer.report')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>34</strong></td>
                                    <td>{{__('Buyer Order Ticket Template')}} <br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Buyer Order Ticket.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.buyer.order.ticket')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>35</strong></td>
                                    <td>{{__('Buyer Extra Service Accept Template')}} <br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Buyer Extra Service Accept.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.buyer.extra.service.accept')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>36</strong></td>
                                    <td>{{__('Admin Payment Status Change Template')}} <br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Admin Payment Status Change.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.payment.status.change.email')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>37</strong></td>
                                    <td>{{__('Admin Send Withdraw Amount Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Admin Send Withdraw Amount.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.payment.withdraw.amount.email')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>38</strong></td>
                                    <td>{{__('Admin Service Approve Template')}} <br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Admin Service Approve.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.service.approve.email')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>39</strong></td>
                                    <td>{{__('Admin Service Assign Template')}} <br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Admin Service Assign.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.service.assign.seller.email')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>40</strong></td>
                                    <td>{{__('Admin Seller Verification Template')}} <br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Admin Seller Verification.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.seller.verification.email')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>41</strong></td>
                                    <td>{{__('Admin To User Verification Code Template')}}<br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Admin To User Verification Code.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.user.verification.code.email')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>42</strong></td>
                                    <td>{{__('Admin To User New Password Template')}} <br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Admin To User New Password.') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.user.new.password.email')"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>43</strong></td>
                                    <td>{{__('New Order Template (All module order included)')}} <br>
                                        <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the New Order Template (All module order included).') }}</span>
                                    </td>
                                    <td>
                                        <x-edit-icon :url="route('admin.new.order.ad.sell.buyer.email')"/>
                                    </td>
                                </tr>
                                @if(moduleExists('JobPost'))
                                    <tr>
                                        <td><strong>44</strong></td>
                                        <td>{{__('Job Create Template')}} <br>
                                            <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Job Create.') }}</span>
                                        </td>
                                        <td>
                                            <x-edit-icon :url="route('admin.job.create.email')"/>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><strong>45</strong></td>
                                        <td>{{__('Job Apply Template')}} <br>
                                            <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Job Apply.') }}</span>
                                        </td>
                                        <td>
                                            <x-edit-icon :url="route('admin.job.apply.email')"/>
                                        </td>
                                    </tr>
                                @endif

                                @if(moduleExists('Subscription'))
                                    <tr>
                                        <td><strong>46</strong></td>
                                        <td>{{__('Membership subscription Template')}} <br>
                                            <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Buy New Subscription.') }}</span>
                                        </td>
                                        <td>
                                            <x-edit-icon :url="route('admin.subscription.buy.email')"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>47</strong></td>
                                        <td>{{__('Renew Subscription Template')}}<br>
                                            <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('For the Renew Subscription.') }}</span>
                                        </td>
                                        <td>
                                            <x-edit-icon :url="route('admin.subscription.renew.email')"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>48</strong></td>
                                        <td>{{__('Subscription Payment Status Update Template')}} <br>
                                            <span class="mt-2"><b class="text-info">{{__('Notes: ')}}</b> {{ __('For the Subscription Payment Status Update.') }}</span>
                                        </td>
                                        <td>
                                            <x-edit-icon :url="route('admin.subscription.payment.status.email')"/>
                                        </td>
                                    </tr>
                                    <!--///buyer-->
                                     <tr>
                                    <td><strong>49</strong></td>
                                    <td>
                                        {{__('buyer  Register Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only buyer  Register.') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-user-register')" />
                                    </td>
                                </tr>
                                    
                                    <tr>
                                    <td><strong>50</strong></td>
                                    <td>
                                        {{__('buyer Job Posting  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer Job Posting .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-Job-posting')" />
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td><strong>51</strong></td>
                                    <td>
                                        {{__('buyer job approved by admin  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when admin approved Job Posting .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-Job-approved-admin')" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>52</strong></td>
                                    <td>
                                        {{__('Buyer New Message  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer new message .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-New-Message')" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>53</strong></td>
                                    <td>
                                        {{__('Buyer Service booking  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they booked service .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-Service-booking')" />
                                    </td>
                                </tr>
                                <!--////-->
                                <tr>
                                    <td><strong>54</strong></td>
                                    <td>
                                        {{__('Buyer Booking approved by freelancer Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when Booking approved by freelancer .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-Service-booking-approved')" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>55</strong></td>
                                    <td>
                                        {{__('Buyer Custom offer sent Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they recived custom offer from freelancer  .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-custom-offer-recieved')" />
                                    </td>
                                </tr><tr>
                                    <td><strong>56</strong></td>
                                    <td>
                                        {{__('Buyer Custom Offer Accepted  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they accept custom offer from freelancer .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-custom-offer-accept')" />
                                    </td>
                                </tr><tr>
                                    <td><strong>57</strong></td>
                                    <td>
                                        {{__('Buyer Custom offer Declined  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they decline custom offer from freelancer .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-custom-offer-decline')" />
                                    </td>
                                </tr><tr>
                                    <td><strong>58</strong></td>
                                    <td>
                                        {{__('Buyer Applying for a job  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they freelancer apply for job request.') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-apply-job-by-freelancer')" />
                                    </td>
                                </tr>
                                <!--?-->
                                <tr>
                                    <td><strong>59</strong></td>
                                    <td>
                                        {{__('Buyer Job hiring  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they hire freelancer for job posted.') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-job-hire-to-freelancer')" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>60</strong></td>
                                    <td>
                                        {{__('Buyer Service completion request Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they freelancer send service completeion request.') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-service-completion-request-from-freelancer')" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>58</strong></td>
                                    <td>
                                        {{__('Buyer Request Modification Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer send Request Modification of the order  ') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-request-modification-for-freelancer')" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>58</strong></td>
                                    <td>
                                        {{__('Buyer Client approves service completion request   Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when  freelancer Client approves service completion request .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-approves-service-completion')" />
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>58</strong></td>
                                    <td>
                                        {{__('Buyer Delivery time extension request  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they freelancer apply for delivery time extension .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-delivery-time-extension-by-freelancer')" />
                                    </td>
                                </tr>
                                
                                 <tr>
                                    <td><strong>59 </strong></td>
                                    <td>
                                        {{__('Buyer Delivery time extension request  approved Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they  approved the  delivery time extension .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-delivery-time-extension-approved-by-freelancer')" />
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>60</strong></td>
                                    <td>
                                        {{__('Buyer Delivery time extension request declined  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they  decline  the  delivery time extension .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-delivery-time-extension-declined-by-freelancer')" />
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>61</strong></td>
                                    <td>
                                        {{__('Buyer Additional service request  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they freelancer send additional service request  .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-additional-service-extension-request')" />
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>62</strong></td>
                                    <td>
                                        {{__('Buyer Additional service request approved  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they arroved the additional service request  .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-additional-service-extension-request-approved')" />
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>63</strong></td>
                                    <td>
                                        {{__('Buyer Additional service request decline  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they decline the additional service request  .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-additional-service-extension-request-decline')" />
                                    </td>
                                </tr>
                                <!--///-->
                                 <tr>
                                    <td><strong>64 1</strong></td>
                                    <td>
                                        {{__('Buyer Client promotes job  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they promote their job posted   .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-promotes-job-request')" />
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>65</strong></td>
                                    <td>
                                        {{__(' When client registers as an enterprise  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they  registers as an enterprise  .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-client-registers-enterprise-request')" />
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>66</strong></td>
                                    <td>
                                        {{__('Buyer Partial payment request  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they recieved  Partial payment request  .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-partial-payment-extension-request')" />
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>67</strong></td>
                                    <td>
                                        {{__('Buyer Partial Payment approved  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they approved  Partial payment request  .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-partial-payment-extension-request-approved')" />
                                    </td>
                                </tr>
                                 <tr>
                                    <td><strong>68</strong></td>
                                    <td>
                                        {{__('Buyer Partial Payment declined  Template')}} <br>
                                    <span class="mt-2"><b class="text-info">{{__('Notes:')}}</b> {{ __('only for buyer when they decline  Partial payment request  .') }}</span>
                                    </td>
                                    <td>
                                      <x-edit-icon :url="route('admin.email.template.dynamic', 'buyer-partial-payment-extension-request-decline')" />
                                    </td>
                                </tr>
                                
                                
                                @endif

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('backend.partials.datatable.script-enqueue')
    <script>
        $(document).ready(function () {
           //to do
        });
    </script>

@endsection
