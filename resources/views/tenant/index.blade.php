
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Teants') }}
            <x-primary-link class="ml-4 float-right" href="{{route('tenants.create')}}" >Create</x-primary-link>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="table">
                        <thead>
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Domain</th>
                          </tr>
                        </thead>
                        <tbody>
                            
                          @foreach ($tenants as $tenant)
                          <tr>
                            <th scope="row">1</th>
                            <td>{{$tenant->name}}</td>
                            <td>{{$tenant->email}}</td>
                            <td>{{$tenant->domain}}</td>
                          </tr>
                          @endforeach
                          
                        
                        </tbody>
                      </table>
                </div>
            </div>
        </div>
    </div>

