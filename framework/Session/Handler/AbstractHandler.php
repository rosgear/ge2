<?php
/**
 * Этот файл является частью пакета Ge Framework.
 * 
 * @link https://rosgear.ru/framework/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Ge\Session\Handler;

use Ge;

/**
 * Абстрактный класс обработчика сессии.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Ge\Session\Handler
 * @since 2.0
 */
abstract class AbstractHandler implements \SessionHandlerInterface
{
    /**
     * Конструктор класса.
     * 
     * @param array $config Параметры конфигурация.
     * 
     * @return void
     */
    public function __construct(array $config = [])
    {
        Ge::configure($this, $config);
    }

    /**
     * Инициализирует сессию.
     * 
     * Повторно инициализирует существующую сессию или создаёт новую. Вызывается когда 
     * сессия стартует или когда вызвана функция `session_start()`.
     * 
     * @see https://www.php.net/manual/ru/sessionhandlerinterface.open
     * 
     * @param string $path Путь, по которому сохраняется/восстанавливается сессия. 
     * @param string $name Название сессии.
     * 
     * @return bool Возвращаемое значение сессионного хранилища (обычно true в случае 
     *     успешного выполнения, `false` в случае возникновения ошибки). Данное значение 
     *     возвращается обратно в PHP для внутренней обработки. 
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Закрывает сессию.
     * 
     * Закрывает текущую сессию. Эта функция автоматически выполняется при закрытии 
     * сессии или явно через `session_write_close()`.
     * 
     * @see https://www.php.net/manual/ru/sessionhandlerinterface.close
     * 
     * @return bool Возвращаемое значение сессионного хранилища (обычно true в случае 
     *     успешного выполнения, `false` в случае возникновения ошибки). Данное значение 
     *     возвращается обратно в PHP для внутренней обработки. 
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Читает данные сессии.
     * 
     * @see https://www.php.net/manual/ru/sessionhandlerinterface.read
     * 
     * @param string $id Идентификатор сессии.
     * 
     * @return string|false Возвращает закодированную строку прочитанных данных. Если 
     *     ничего не прочитано, возвращается `false`.
     */
    public function read(string $id): string|false
    {
        return '';
    }

    /**
     * Записать данные сессии.
     * 
     * @see https://www.php.net/manual/ru/sessionhandlerinterface.write
     * 
     * @param string $id Идентификатор сессии.
     * @param string $data Закодированные данные сессии. Эти данные являются результатом 
     *     внутреннего кодирования PHP суперглобального массива $_SESSION в сериализованную 
     *     строку и передачи её в качестве этого параметра.
     * 
     * @return bool Возвращаемое значение сессионного хранилища (обычно true в случае успешного 
     *     выполнения, `false` в случае возникновения ошибки). Данное значение возвращается обратно 
     *     в PHP для внутренней обработки. 
     */
    public function write(string $id, string $data): bool
    {
        return true;
    }

    /**
     * Уничтожает сессию.
     * 
     * @see https://www.php.net/manual/ru/sessionhandlerinterface.destroy
     * 
     * @param string $id Идентификатор сессии уничтожается.
     * 
     * @return bool Возвращаемое значение сессионного хранилища (обычно true в случае 
     *     успешного выполнения, `false` в случае возникновения ошибки). Данное значение 
     *     возвращается обратно в PHP для внутренней обработки. 
     */
    public function destroy(string $id): bool
    {
        return true;
    }

    /**
     * Очищает старые сессии.
     * 
     * @see https://www.php.net/manual/ru/sessionhandlerinterface.gc
     * 
     * @param int $maxLifetime Сессии, которые не обновлялись в течение max_lifetime 
     *     секунд, будут удалены. 
     * 
     * @return int|false Возвращает количество удалённых сессий в случае успешного 
     *     выполнения или `false` в случае возникновения ошибки. 
     */
    public function gc(int $maxLifetime): int|false
    {
        return true;
    }
}
