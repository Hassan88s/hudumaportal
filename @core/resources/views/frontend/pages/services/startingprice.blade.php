 @if(is_null($service->starting_price))
                                                         {{ amount_with_currency_symbol($service->price) }}
                                                            @else
                                                          {{ amount_with_currency_symbol($service->starting_price) }}
                                                            @endif