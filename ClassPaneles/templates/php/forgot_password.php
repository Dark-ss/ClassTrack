<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "DM Sans", sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .recovery-container {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }

        .recovery-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #1dc0c9, #1a93a0);
        }

        .icon-container {
            width: 70px;
            height: 70px;
            background: rgba(29, 192, 201, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 1.5rem;
        }

        .icon-container i {
            font-size: 2rem;
            color: #1dc0c9;
        }

        h1 {
            color: #2d3748;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .description {
            color: #718096;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }

        input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        input:focus {
            outline: none;
            border-color: #1dc0c9;
            background: white;
            box-shadow: 0 0 0 3px rgba(29, 192, 201, 0.1);
        }

        button {
            width: 100%;
            padding: 1rem;
            background: #1dc0c9;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        button:hover {
            background: #1a93a0;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(29, 192, 201, 0.15);
        }

        button:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            color: #718096;
            text-decoration: none;
            font-size: 0.9rem;
            display: block;
        }

        .back-link:hover {
            color: #1dc0c9;
        }

        @media (max-width: 480px) {
            .recovery-container {
                padding: 1.5rem;
            }

            .icon-container {
                width: 60px;
                height: 60px;
            }

            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="icon-container">
            <i class="fas fa-lock"></i>
        </div>
        <h1>¿Olvidaste tu contraseña?</h1>
        <p class="description">No te preocupes, ingresa tu correo electrónico y te enviaremos las instrucciones para recuperar tu cuenta.</p>
        
        <form action="send_reset_link.php" method="POST">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input 
                    type="email" 
                    name="correo" 
                    placeholder="correo@ejemplo.com" 
                    required 
                    autocomplete="email">
            </div>
            <button type="submit">
                Recuperar Contraseña
            </button>
        </form>
        
        <a href="http://localhost/ClassTrack-master/ClassPaneles/templates/index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
        </a>
    </div>
</body>
</html>
