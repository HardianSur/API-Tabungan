<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Tabungan</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body>
    <section class="bg-gray-50 dark:bg-gray-900">
        <div class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
            <a href="#" class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
                <img class="w-8 h-8 mr-2" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/logo.svg"
                    alt="logo">
                TabunganKu
            </a>
            <div
                class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1
                        class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
                        Sign in to your account
                    </h1>
                    <form class="space-y-4 md:space-y-6" id="form-auth" method="POST" autocomplete="off">
                        @csrf
                        <div>
                            <label for="username"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                            <input type="username" name="username" id="username"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="example123" required>
                        </div>
                        <div>
                            <label for="password"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                            <input type="password" name="password" id="password" placeholder="••••••••"
                                class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                required>
                        </div>
                        <div class="flex items-center justify-between">
                            <a href="#"
                                class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-500">Forgot
                                password?</a>
                        </div>
                        <button type="submit"
                            class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800" id="submit">Sign
                            in</button>
                        <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                            Don’t have an account yet? <a href="#"
                                class="font-medium text-primary-600 hover:underline dark:text-primary-500">Sign up</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script>
        document.getElementById("password").addEventListener("keydown", function(event) {
            if (event.keyCode === 13) {
                event.preventDefault();

                var form = $('#form-auth')[0];
                console.log($('#form-auth').serializeArray());
                if (form.checkValidity()) {
                    $.ajax({
                        url: "{{ url('auth/login/signin') }}",
                        type: "POST",
                        data: $('#form-auth').serializeArray()
                    }).done(function(response) {
                        // notification(response.message, response.status)

                        if(response.status == 200 || response.status == 201){
                            setTimeout(() => {
                                window.location.href = "{{ url('/') }}";
                            }, 500);
                        }
                    }).catch(error => {
                        // notification(error.statusText, error.status)
                    });

                } else {
                    return;
                }
            }
        });


        $('#form-auth').submit(function(e) {
            e.preventDefault()

            var form = $('#form-auth')[0];
            console.log($('#form-auth').serializeArray());
            if (form.checkValidity()) {
                $.ajax({
                    url: "{{ url('auth/login/signin') }}",
                    type: "POST",
                    data: $('#form-auth').serializeArray()
                }).done(function(response) {
                    // notification(response.message, response.status)
                    console.log(response.message, response.status,response.data);

                    if(response.status == 200 || response.status == 201){
                        setTimeout(() => {
                            window.location.href = "{{ url('admin/dashboard') }}";
                        }, 500);
                    }
                }).catch(error => {
                    // notification(error.statusText, error.status)
                });
            } else {
                return;
            }

        });
    </script>
</body>

</html>
