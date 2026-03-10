<?php

namespace ChurchCRM\Plugins\Catequese\Service;

use ChurchCRM\model\ChurchCRM\Group;
use ChurchCRM\model\ChurchCRM\GroupQuery;
use ChurchCRM\model\ChurchCRM\Person2group2roleP2g2r;
use ChurchCRM\model\ChurchCRM\Person2group2roleP2g2rQuery;
use ChurchCRM\Service\GroupService;

class MatriculaService
{
    private GroupService $groupService;

    public function __construct()
    {
        $this->groupService = new GroupService();
    }

    /**
     * Get all members from a group
     */
    public function getGroupMembers(int $groupId): array
    {
        $members = Person2group2roleP2g2rQuery::create()
            ->filterByGroupId($groupId)
            ->joinWithPerson()
            ->find();

        $result = [];
        foreach ($members as $member) {
            $person = $member->getPerson();
            if ($person !== null) {
                $result[] = [
                    'person_id' => $person->getId(),
                    'name' => $person->getFullName(),
                    'role_id' => $member->getRoleId(),
                ];
            }
        }

        return $result;
    }

    /**
     * Migrate members from one group to another (rematrícula)
     */
    public function migrarMembros(int $grupoOrigemId, int $grupoDestinoId, array $personIds): array
    {
        $migrated = [];
        $errors = [];

        foreach ($personIds as $personId) {
            try {
                // Remove from old group
                $oldMembership = Person2group2roleP2g2rQuery::create()
                    ->filterByGroupId($grupoOrigemId)
                    ->filterByPersonId((int)$personId)
                    ->findOne();

                if ($oldMembership !== null) {
                    $roleId = $oldMembership->getRoleId();
                    $oldMembership->delete();

                    // Add to new group with same role
                    $newMembership = new Person2group2roleP2g2r();
                    $newMembership->setGroupId($grupoDestinoId);
                    $newMembership->setPersonId((int)$personId);
                    $newMembership->setRoleId($roleId);
                    $newMembership->save();

                    // Reset group-specific properties for new enrollment
                    $this->resetGroupProperties($grupoDestinoId, (int)$personId);

                    $migrated[] = $personId;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'person_id' => $personId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'migrated' => $migrated,
            'errors' => $errors,
        ];
    }

    /**
     * Reset group-specific properties for new enrollment
     */
    private function resetGroupProperties(int $groupId, int $personId): void
    {
        // This will set status_matricula to 'Inscrito' and reset taxa_paga
        // Implementation depends on how group-specific properties are stored
        // For now, this is a placeholder - will be implemented based on actual schema
    }

    /**
     * Get catequese groups by year
     */
    public function getGruposPorAno(?int $ano = null): array
    {
        $query = GroupQuery::create()
            ->filterByType(3); // Assuming 3 is Catequese type

        if ($ano !== null) {
            // Filter by year in group name or custom property
            $query->filterByName("%$ano%");
        }

        $groups = $query->find();

        $result = [];
        foreach ($groups as $group) {
            $result[] = [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'type' => $group->getType(),
                'member_count' => $this->groupService->getGroupMemberCount($group->getId()),
            ];
        }

        return $result;
    }

    /**
     * Create new catequese group for a year
     */
    public function criarGrupo(string $nome, int $ano): ?Group
    {
        $group = new Group();
        $group->setName($nome);
        $group->setType(3); // Catequese type
        $group->setDescription("Grupo de catequese para o ano letivo $ano");
        $group->save();

        return $group;
    }
}
