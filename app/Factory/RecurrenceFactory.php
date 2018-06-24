<?php
/**
 * RecurrenceFactory.php
 * Copyright (c) 2018 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace FireflyIII\Factory;


use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Models\Recurrence;
use FireflyIII\Services\Internal\Support\RecurringTransactionTrait;
use FireflyIII\Services\Internal\Support\TransactionServiceTrait;
use FireflyIII\Services\Internal\Support\TransactionTypeTrait;
use FireflyIII\User;

/**
 * Class RecurrenceFactory
 */
class RecurrenceFactory
{
    use TransactionTypeTrait, TransactionServiceTrait, RecurringTransactionTrait;

    /** @var User */
    private $user;

    /**
     * @param array $data
     *
     * @throws FireflyException
     * @return Recurrence
     */
    public function create(array $data): Recurrence
    {
        $type        = $this->findTransactionType(ucfirst($data['recurrence']['type']));
        $repetitions = (int)$data['recurrence']['repetitions'];
        $recurrence  = new Recurrence(
            [
                'user_id'             => $this->user->id,
                'transaction_type_id' => $type->id,
                'title'               => $data['recurrence']['title'],
                'description'         => $data['recurrence']['description'],
                'first_date'          => $data['recurrence']['first_date']->format('Y-m-d'),
                'repeat_until'        => $repetitions > 0 ? null : $data['recurrence']['repeat_until'],
                'latest_date'         => null,
                'repetitions'         => $data['recurrence']['repetitions'],
                'apply_rules'         => $data['recurrence']['apply_rules'],
                'active'              => $data['recurrence']['active'],
            ]
        );
        $recurrence->save();

        $this->updateMetaData($recurrence, $data);
        $this->createRepetitions($recurrence, $data['repetitions'] ?? []);
        $this->createTransactions($recurrence, $data['transactions'] ?? []);

        return $recurrence;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

}